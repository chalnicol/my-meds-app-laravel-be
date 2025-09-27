<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\MedicationResource;
use App\Http\Resources\StockResource;
use Illuminate\Support\Facades\DB; // <-- Don't forget this import
use Illuminate\Support\Facades\Auth;

use App\Models\Medication;
use App\Models\Stock;
use App\Models\MedicationIntake;


use Carbon\Carbon;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $searchTerm = $request->query('search');

        $medications = Medication::where('user_id', $request->user()->id)
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('brandName', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('genericName', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('dosage', 'LIKE', '%' . $searchTerm . '%');
                });
            })
             ->orderBy('created_at', 'desc')
             ->paginate(10);

        return MedicationResource::collection($medications);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
         $request->validate([
            'brandName' => 'required|string|max:255',
            'genericName' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'frequencyType' => 'required|string|in:Everyday,SpecificDays',
            'frequency' => 'required_if:frequencyType,SpecificDays|nullable|array',
            'dailySchedule' => 'required|array',
            'remainingStock' => 'required|integer|min:0'
        ]);

        // $user = $request->user();

        $medication = Medication::create([
            'user_id' => $request->user()->id,
            'brandName' => ucwords($request->brandName),
            'genericName' => ucwords($request->genericName),
            'dosage' => $request->dosage,
            'status' => $request->status,
            'frequencyType' => $request->frequencyType,
            'frequency' => $request->frequencyType === 'SpecificDays' ? $request->frequency : null,
            'dailySchedule' => $request->dailySchedule,
            'remainingStock' => $request->remainingStock
        ]);

        return response()->json([
            'message' => 'Medication added successfully.',
            'medication' => $medication
        ], 201);
    

    }

    /**
     * Display the specified resource.
     */
    public function show(Medication $medication)
    {
      
        // Use loadAggregate to perform the sums
        $medication->loadAggregate('stocks', 'quantity', 'sum'); // This adds stocks_sum_quantity
        
        // For the total value, use DB::raw to perform the multiplication in the query
        $medication->loadAggregate('stocks', DB::raw('price * quantity'), 'sum');
        
        //
        return response()->json([
            'message' => 'Medication fetched successfully.',
            'medication' => new MedicationResource($medication)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medication $medication)
    {
        //
         $request->validate([
            'brandName' => 'required|string|max:255',
            'genericName' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'frequencyType' => 'required|string|in:Everyday,SpecificDays',
            'frequency' => 'required_if:frequencyType,SpecificDays|nullable|array',
            'dailySchedule' => 'required|array',
        ]);

        // $user = $request->user();

        $medication->update([
            'brandName' => ucwords($request->brandName),
            'genericName' => ucwords($request->genericName),
            'dosage' => $request->dosage,
            'status' => $request->status,
            'frequencyType' => $request->frequencyType,
            'frequency' => $request->frequencyType === 'SpecificDays' ? $request->frequency : null,
            'dailySchedule' => $request->dailySchedule,
        ]);

        return response()->json([
            'message' => 'Medication updated successfully.',
            'medication' => $medication
        ], 201);

        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medication $medication)
    {
        //
        $medication->delete();
        return response()->json([
            'message' => 'Medication deleted successfully.'
        ]);
    }

    public function toggleStatus (Request $request, Medication $medication) 
    {
        
        $newStatus = $medication->status === 'Active' ? 'Inactive' : 'Active';

        $medication->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Medication status updated successfully.'
        ]);
    }

    


    public function getStocks (Request $request, Medication $medication) 
    {
        $searchTerm = $request->query('search');

        $stocks = $medication->stocks()

            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('source', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('price', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('quantity', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('created_at', 'LIKE', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return StockResource::collection($stocks);

    }

    public function addStock (Request $request, Medication $medication) 
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric:strict|min:1',
            'source' => 'required|min:2|max:255'
        ]);

        $medication->update([
            'remainingStock' => $medication->remainingStock + $request->quantity
        ]);

        $stock = $medication->stocks()->create([
            'user_id' => $request->user()->id,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'source' => $request->source
        ]);

        return response()->json([
            'message' => 'Restock is done successfully.',
            'stock' => new StockResource($stock)
        ]);
    }

    public function updateStock (Request $request, Medication $medication, Stock $stock) 
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric:strict|min:1',
            'source' => 'required|min:2|max:255'
        ]);

        $oldRemainingStock = $stock->quantity;

        $stock->update([
            'quantity' => $request->quantity,
            'price' => $request->price,
            'source' => $request->source
        ]);

        $medication->update([
            'remainingStock' => $medication->remainingStock + $request->quantity - $oldRemainingStock
        ]);

        return response()->json([
            'message' => 'Stock updated successfully.',
            'stock' => new StockResource($stock)
        ]);


    }

    // public function getTodaysMeds () 
    // {

    //     $dayOfWeek = Carbon::now()->dayOfWeek;
    //     // $dayOfWeek = Carbon::create(2025, 9, 28, 0, 0, 0)->dayOfWeek;

    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'No authenticated user',
    //         ]);
    //     }

    //     $medications = $user->medications()
    //         ->where('frequencyType', 'Everyday')
    //         ->orWhere('frequency', 'like', "%{$dayOfWeek}%")
    //         ->get();

    //     // Add the 'is_taken' flag to each medication
    //     $medsWithStatus = $medications->map(function ($medication) use ($user, $dayOfWeek) {
    //         $isTaken = MedicationIntake::where('user_id', $user->id)
    //             ->where('medication_id', $medication->id)
    //             ->whereDate('taken_at', Carbon::today())
    //             ->exists();

    //         $medication->is_taken = $isTaken;

    //         return $medication;
    //     });

    //     return response()->json([
    //         'message' => 'Medications fetched successfully.',
    //         'medications' => MedicationResource::collection($medications)
    //     ]);
        
    // }

    public function getTodaysMeds()
    {
        
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['message' => 'No authenticated user'], 401);
        }

        $now = Carbon::now($user->timezone);
        $dayOfWeek = $now->dayOfWeek;

        // Fetch all medications for the user.
       $medications = Medication::where('user_id', $user->id)
        ->where('status', 'Active')
        ->where(function ($query) use ($dayOfWeek) {
            $query->where('frequencyType', 'Everyday')
                  ->orWhere('dailySchedule', 'like', "%{$dayOfWeek}%");
        })
        ->get();
    
        // Fetch all of today's intake records for the user.
        $intakeRecords = MedicationIntake::where('user_id', $user->id)
            ->whereDate('taken_at', $now->toDateString())
            ->get();
    
        return response()->json([
            'message' => 'Medications fetched successfully.',
            'medications' => MedicationResource::collection($medications),
            'intake_records' => $intakeRecords, // Pass the intake records to the frontend
        ]);
    }
    
    public function takeMedication(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No authenticated user'], 401);
        }

        $request->validate([
            'medicationId' => 'required|exists:medications,id',
            'scheduledTime' => 'required|string', // The new required field
        ]);


        $medication = Medication::find($request->medicationId);
        
        $existingIntake = MedicationIntake::where('user_id', $user->id)
            ->where('medication_id', $request->medicationId)
            ->where('scheduled_time', $request->scheduledTime) // Check for the specific time
            ->whereDate('taken_at', Carbon::today())
            ->first();

        if ($existingIntake) {

            $medication->update([
                'remainingStock' => $medication->remainingStock + 1
            ]);

            $existingIntake->delete();
            return response()->json(['message' => 'Medication intake removed successfully.']);


        } else {

            if ($medication->remainingStock <= 0) {
                return response()->json(['message' => 'Medication is out of stock.'], 400);
            }

            $medication->update([
                'remainingStock' => $medication->remainingStock - 1
            ]);
            
            MedicationIntake::create([
                'user_id' => $user->id,
                'medication_id' => $request->medicationId,
                'scheduled_time' => $request->scheduledTime, // Save the specific time
                'taken_at' => Carbon::now(),
            ]);
            return response()->json(['message' => 'Medication intake saved successfully.']);
        }
    }

    

  

}
