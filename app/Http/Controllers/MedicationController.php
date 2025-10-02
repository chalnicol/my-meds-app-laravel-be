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
use App\Models\TimeSchedule; // NEW

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

        $medications = Medication::with('timeSchedules')
            ->where('user_id', $request->user()->id)
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('brand_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('generic_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('dosage', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('status', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('frequency_type', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('drug_form', 'LIKE', '%' . $searchTerm . '%');
                    
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
            'brandName' => 'required|string|max:255|unique:medications,brand_name',
            'genericName' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'drugForm' => 'required|string|in:Tablet,Capsule,Liquid,Drops,Injection,Cream,Syrup,Ointment,Suppository,Other',
            'status' => 'required|string|in:Active,Inactive',
            'frequencyType' => 'required|string|in:Everyday,SpecificDays',
            'frequency' => 'required_if:frequencyType,SpecificDays|nullable|array',
            'timeSchedules' => 'required|array',
            'remainingStock' => 'required|integer|min:0'
        ]);

        // dd ($request);
        // $user = $request->user();

        $medication = Medication::create([
            'user_id' => $request->user()->id,
            'brand_name' => ucwords($request->brandName),
            'generic_name' => ucwords($request->genericName),
            'dosage' => $request->dosage,
            'drug_form' => $request->drugForm,
            'status' => $request->status,
            'frequency_type' => $request->frequencyType,
            'frequency' => $request->frequencyType === 'SpecificDays' ? $request->frequency : null,
            'remaining_stock' => $request->remainingStock
        ]);

        if ($medication) {

            $is_countable = $medication->drug_form == 'Tablet' ||  $medication->drug_form == 'Capsule';

            foreach ($request->timeSchedules as $schedule) {

                $newQuantity = $is_countable ? $schedule['quantity'] : null;

                $newTimeSchedule = Carbon::parse($schedule['schedule_time'])->format('H:i');

                $medication->timeSchedules()->create([
                    'schedule_time' => $newTimeSchedule,
                    'quantity' => $newQuantity,
                ]);
            }
        }

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
        
        $medication->load('timeSchedules');

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
            'brandName' => 'required|string|max:255|unique:medications,brand_name,'.$medication->id, 
            'genericName' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'drugForm' => 'required|string|in:Tablet,Capsule,Liquid,Drops,Injection,Cream,Syrup,Ointment,Suppository,Other',
            'status' => 'required|string|in:Active,Inactive',
            'frequencyType' => 'required|string|in:Everyday,SpecificDays',
            'frequency' => 'required_if:frequencyType,SpecificDays|nullable|array',
            'timeSchedules' => 'required|array',
        ]);

        // $user = $request->user();

        $medication->update([
            'brand_name' => ucwords($request->brandName),
            'generic_name' => ucwords($request->genericName),
            'dosage' => $request->dosage,
            'drug_form' => $request->drugForm,
            'status' => $request->status,
            'frequency_type' => $request->frequencyType,
            'frequency' => $request->frequencyType === 'SpecificDays' ? $request->frequency : null,
        ]);

        $is_countable = $medication->drug_form == 'Tablet' ||  $medication->drug_form == 'Capsule';

        $submitted_schedule_ids = [];

        foreach ($request->timeSchedules as $schedule) {

            $newQuantity = $is_countable ? $schedule['quantity'] : null;

            $newTimeSchedule = Carbon::parse($schedule['schedule_time'])->format('H:i');

            $timeSchedule = $medication->timeSchedules()->updateOrCreate(
                [
                    'id' => $schedule['id']
                ],
                [
                    'schedule_time' => $newTimeSchedule,
                    'quantity' => $newQuantity,
                ]
            );

            $submitted_schedule_ids[] = $timeSchedule->id;
        }

        $medication->timeSchedules()
            ->whereNotIn('id', $submitted_schedule_ids)
            ->delete();
        
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
            'remaining_stock' => $medication->remaining_stock + $request->quantity
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
            'remaining_stock' => $medication->remaining_stock + $request->quantity - $oldRemainingStock
        ]);

        return response()->json([
            'message' => 'Stock updated successfully.',
            'stock' => new StockResource($stock)
        ]);


    }

    public function getTodaysMeds()
    {
        
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['message' => 'No authenticated user'], 401);
        }

        $now = Carbon::now($user->timezone);
        // $dayOfWeek = $now->dayOfWeek;
        $dayOfWeekIndex = (string) $now->dayOfWeek; 
        $todayDate = $now->toDateString();

        // Fetch all medications for the user.
       $medications = Medication::with('timeSchedules')
        ->where('user_id', $user->id)
        ->where('status', 'Active')
        ->where(function ($query) use ($dayOfWeekIndex) {
            $query->where('frequency_type', 'Everyday')
                ->orWhereJsonContains('frequency', $dayOfWeekIndex); 
        })
        ->get();
    
        // Fetch all of today's intake records for the user.
        $intakeRecords = MedicationIntake::where('user_id', $user->id)
            ->whereDate('taken_at', $todayDate)
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
            'timeScheduleId' => 'required|integer|exists:time_schedules,id', // The new required field
        ]);

        $schedule = TimeSchedule::with('medication')
            ->where('id', $request->timeScheduleId)
            ->first();
        
        if (!$schedule || $schedule->medication->user_id !== $user->id) {
            return response()->json(['message' => 'Schedule not found or unauthorized.'], 403);
        }

        $medication = $schedule->medication;
        $dosage = $schedule->quantity; // The amount to add/subtract
        $todayDate = Carbon::today()->toDateString();

        $existingIntake = MedicationIntake::where('user_id', $user->id)
            ->where('time_schedule_id', $request->timeScheduleId) // Check for the specific time
            ->whereDate('taken_at', $todayDate)
            ->first();

        if ($existingIntake) {

            $medication->increment('remaining_stock', $dosage); 

            $existingIntake->delete();
            return response()->json(['message' => 'Medication intake removed successfully.']);

        } else {

            if ($medication->remaining_stock <= 0) {
                return response()->json(['message' => 'Medication is out of stock.'], 400);
            }

            $medication->decrement('remaining_stock', $dosage); 
            
            MedicationIntake::create([
                'user_id' => $user->id,
                'time_schedule_id' => $schedule->id,
                'taken_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Medication intake saved successfully.']);
        }
    }

    // public function getTodaysMeds()
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json(['message' => 'No authenticated user'], 401);
    //     }

    //     // 1. Determine today's date, time, and day of the week based on user's timezone.
    //     $now = Carbon::now($user->timezone ?? config('app.timezone')); // Use user's timezone or default
    //     // $dayOfWeek = $now->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
    //     $todayDate = $now->toDateString();
    //     $dayOfWeekIndex = (string) $now->dayOfWeek; 

    //     // 2. Fetch all TODAY'S ACTIVE medications with their schedules
    //     $medications = Medication::with('timeSchedules') // Eager load the new schedule table
    //         ->where('user_id', $user->id)
    //         ->where('status', 'Active')
    //         ->where(function ($query) use ($dayOfWeekIndex) {
    //             $query->where('frequency_type', 'Everyday')
    //                 // Check if the current day's index exists in the JSON 'frequency' array
    //                 ->orWhereJsonContains('frequency', $dayOfWeekIndex); 
    //         })
    //         ->get();
        

    //     // 3. Fetch all of today's intake records for the user.
    //     $intakeRecords = MedicationIntake::where('user_id', $user->id)
    //         ->whereDate('taken_at', $todayDate)
    //         ->get()
    //         ->keyBy('time_schedule_id'); // Key the collection by schedule ID for easy lookup
        
    //     $todaysSchedule = collect();

    //     foreach ($medications as $medication) {
    //         foreach ($medication->timeSchedules as $schedule) {
                
    //             // Check if an intake record exists for this specific schedule ID today
    //             $intake = $intakeRecords->get($schedule->id);

    //             $todaysSchedule->push([
    //                 // Schedule Details (The dose information)
    //                 'schedule_id' => $schedule->id,
    //                 'schedule_time' => $schedule->schedule_time,
    //                 'dosage_quantity' => $schedule->quantity,
                    
    //                 // Medication Details
    //                 'medication_id' => $medication->id,
    //                 'brand_name' => $medication->brand_name,
    //                 'generic_name' => $medication->generic_name,
    //                 'remaining_stock' => $medication->remaining_stock,

    //                 // Intake Status
    //                 'is_taken' => (bool) $intake,
    //                 'taken_at' => $intake ? $intake->taken_at : null,
    //             ]);
    //         }
    //     }

    //     // Sort the final list by scheduled time
    //     $todaysSchedule = $todaysSchedule->sortBy('schedule_time')->values();

    //     return response()->json([
    //         'message' => 'Today\'s medication schedule fetched successfully.',
    //         'schedules' => $todaysSchedule,
    //     ]);
    // }

    // public function takeMedication(Request $request)
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json(['message' => 'No authenticated user'], 401);
    //     }

    //     $request->validate([
    //         // We now require the ID of the time_schedule entry
    //         'time_schedule_id' => 'required|exists:time_schedules,id',
    //     ]);

    //     // 1. Get the schedule and related medication in one query
    //     $schedule = TimeSchedule::with('medication')
    //         ->where('id', $request->time_schedule_id)
    //         ->first();

    //     if (!$schedule || $schedule->medication->user_id !== $user->id) {
    //         return response()->json(['message' => 'Schedule not found or unauthorized.'], 403);
    //     }

    //     $medication = $schedule->medication;
    //     $dosage = $schedule->quantity; // The amount to add/subtract
    //     $todayDate = Carbon::today()->toDateString();

    //     // 2. Check if the dose has already been taken today for this specific schedule.
    //     $existingIntake = MedicationIntake::where('user_id', $user->id)
    //         ->where('time_schedule_id', $schedule->id)
    //         ->whereDate('taken_at', $todayDate)
    //         ->first();

    //     // Use a database transaction to ensure stock and intake are updated together
    //     // DB::beginTransaction();
    //     // try {
    //         if ($existingIntake) {
    //             // ACTION: UNTAKE (Remove the intake record and restore stock)
                
    //             // Increment remaining stock by the dose amount
    //             $medication->increment('remaining_stock', $dosage); 
                
    //             $existingIntake->delete();
    //             $message = 'Medication intake removed successfully. Stock restored.';
    //         } else {
    //             // ACTION: TAKE (Create the intake record and subtract stock)

    //             if ($medication->remaining_stock < $dosage) {
    //                 return response()->json([
    //                     'message' => 'Medication is out of stock.',
    //                     'required' => $dosage,
    //                     'available' => $medication->remaining_stock
    //                 ], 400);
    //             }

    //             // Decrement remaining stock by the dose amount
    //             $medication->decrement('remaining_stock', $dosage);

    //             // Create the intake record linked to the schedule ID
    //             MedicationIntake::create([
    //                 'user_id' => $user->id,
    //                 'time_schedule_id' => $schedule->id,
    //                 'taken_at' => Carbon::now(),
    //             ]);
    //             $message = 'Medication intake saved successfully. Stock updated.';
    //         }

    //         // DB::commit();
    //         return response()->json(['message' => $message]);

    //     // } catch (\Exception $e) {
    //     //     DB::rollBack();
    //     //     // Log the error $e->getMessage() for debugging
    //     //     return response()->json(['message' => 'An error occurred during transaction.'], 500);
    //     // }
    // }

}
