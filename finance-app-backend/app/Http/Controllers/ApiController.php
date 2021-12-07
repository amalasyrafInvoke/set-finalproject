<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\JsonTrait;

use App\Http\Resources\UserResource;
use App\Models\Account;
use App\Models\Saving;
use App\Models\SavingTransaction;
use App\Models\Transaction;
use App\Models\User;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiController extends Controller
{
  //
  use JsonTrait;

  /**
   * Create a new AuthController instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  /**
   * Login API
   * @bodyParam email string required The email of the user. Example: superadmin@invoke.com
   * @bodyParam password string required The password of the user. Example: password
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors(), 422);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter',
        422
      );
    }

    if (!$token = Auth::attempt($validator->validated())) {
      // return response()->json(['error' => 'Unauthorized HEHEHE'], 401);
      return $this->jsonResponse(
        $validator->errors(),
        'The email and password does not match our records',
        401
      );
    }

    return $this->createNewToken($token);
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout()
  {
    auth()->logout();

    return response()->json(['message' => 'User successfully signed out']);
  }

  /**
   * Register a User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email|max:255|unique:users',
      'name' => 'required|string|between:2,255',
      'password' => 'required|string|confirmed|min:6',
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    $user = User::create(array_merge(
      $validator->validated(),
      ['password' => bcrypt($request->password)]
    ));

    return $this->jsonResponse(
      $user,
      'User successfully registered',
      200
    );
  }

  /**
   * Refresh a token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refresh()
  {
    return $this->createNewToken(auth()->refresh());
  }

  /**
   * Get the authenticated User
   * 
   * Method: GET
   * 
   * The API endpoint for getting the authenticated User
   * Route: /auth/user-profile
   * 
   * In 200 status, will receive the current authenticated 
   * user information from the response
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function userProfile()
  {
    // return response()->json(auth()->user());
    // $account = Account::where('id', auth()->user()->id)->first();
    $user = User::where('id', auth()->user()->id)->with('account')->first();
    // error_log($user);
    return $this->jsonResponse(
      $user,
      'User profile successfully fetched.',
      200
    );
  }

  /**
   * Update the user profile
   * 
   * Method: POST
   * 
   * The API endpoint for updating the User
   * Route: /auth/update-user
   * 
   * In 200 status, will update the user information from the request
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function updateUser(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'contact_number' => 'required',
      'dob' => 'required'
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    $user = User::where('id', auth()->user()->id)->update(['contact_number' => $request->contact_number, 'dob' => $request->dob]);

    return $this->jsonResponse(
      $user,
      'Successfully update the user.',
      200
    );
  }

  /**
   * Get the token array structure.
   *
   * @param  string $token
   *
   * @return \Illuminate\Http\JsonResponse
   */
  protected function createNewToken($token)
  {
    // return response()->json([
    //   'access_token' => $token,
    //   'token_type' => 'bearer',
    //   'expires_in' => auth('api')->factory()->getTTL() * 60,
    //   'user' => auth()->user()
    // ]);

    return $this->jsonResponse(
      [
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 60,
        'user' => User::where('id', auth()->user()->id)->with('account')->first()
        // 'user' => auth()->user()
      ],
      'Login successful',
      200
    );
  }

  /**
   * Get a Wallet Balance for the user
   * 
   * Method: GET
   * 
   * The API endpoint for getting the wallet balance of the user
   * Route: /fetch-wallet/{id}
   * 
   * In 200 status, will return the current wallet balance in the response
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function fetchWallet(Request $request)
  {
    $balance = Account::where('id', $request->id)->value('balance');

    return $this->jsonResponse(
      $balance,
      'User Wallet Balance successfully fetched',
      200
    );
  }

  /**
   * Create a Wallet Account for the user
   * 
   * Method: POST
   * 
   * The API endpoint for getting the authenticated User
   * Route: /create-wallet
   * 
   * In 200 status, a wallet account will be created related to the user
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function createWallet(Request $request)
  {
    $account = Account::create([
      'user_id' => auth()->user()->id,
      'number' => random_int(10000001, 99999999),
    ]);

    return $this->jsonResponse(
      $account,
      'User Wallet Account successfully created',
      200
    );
  }

  /**
   * Get All Transactions Related to the user
   * 
   * Method: GET
   * 
   * The API endpoint for getting the list of transactions of the user
   * Route: /transactions/all/{accountId}
   * 
   * @urlParam accountId integer required The account ID of the user. Example: 1
   * 
   * In 200 status, the list of transactions related to the user is fetched
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function getTransactionsList(Request $request)
  {
    if ($request->accountId == false) {
      return $this->jsonResponse(
        [],
        'User transactions successfully fetched.',
        200
      );
    }
    $transactionsAll = Transaction::latest()->where('account_id', $request->accountId)->get();

    return $this->jsonResponse(
      $transactionsAll,
      'User transactions successfully fetched.',
      200
    );
  }

  /**
   * Get Last 7 Days Transactions Related to the user
   * 
   * Method: GET
   * 
   * The API endpoint for getting the list of transactions of the user
   * Route: /transactions/pastSevenDays/{accountId}
   * 
   * @urlParam accountId integer required The account ID of the user. Example: 1
   * 
   * In 200 status, the list of transactions of the last 7 days is fetched
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSevenDaysTransactions(Request $request)
  {
    if ($request->accountId == false) {
      return $this->jsonResponse(
        [],
        'User Wallet Account Does Not Exist.',
        422
      );
    }

    for ($i = 0; $i < 7; $i++) {
      global $sevenDaysArr;
      $date = Carbon::now()->subDays($i)->startOfDay();
      $date2 = Carbon::now()->subDays($i)->endOfDay();

      $theDate = Carbon::now()->subDays($i);
      $income =
        Transaction::select(DB::Raw('sum(amount) as totalAmount'))->where('account_id', $request->accountId)
        ->whereBetween('created_at', [$date, $date2])
        ->where('process_type', 'INCOME')
        ->get();


      $expenses =
        Transaction::select(DB::Raw('sum(amount) as totalAmount'))->where('account_id', $request->accountId)
        ->whereBetween('created_at', [$date, $date2])
        ->where('process_type', 'EXPENSES')
        ->get();

      $new_array[] = array("date" => $theDate->format('d-m-Y'), "income"=> $income[0]->totalAmount ?? 0, "expenses"=>$expenses[0] ->totalAmount ?? 0);
    }

    return $this->jsonResponse(
      $new_array,
      'Last 7 days transactions successfully fetched.',
      200
    );
  }

  /**
   * Create a New Transactions
   * 
   * Method: POST
   * 
   * The API endpoint for creating a new transaction for the user
   * Route: /transactions/create/{accountId}
   * 
   * @urlParam accountId integer required The account ID of the user. Example: 1
   * 
   * In 200 status, will insert a new records of transaction into the DB,
   * and return the transaction info
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function createTransactions(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'amount' => 'required|numeric|min:0.01',
      'details' => 'nullable',
      'process_type' => 'required',
      // 'account_id' => 'required',
      'status' => 'string'
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    $balance = Account::where('id', $request->accountId)->value('balance');

    if ($request->process_type === 'INCOME') {
      $updateBalance = Account::where('id', $request->accountId)->update(['balance' => $balance + $request->amount]);
    }

    if ($request->process_type === 'EXPENSES') {
      if ($balance - $request->amount < 0) {
        return $this->jsonResponse(
          $balance,
          'You dont have enough balance to perform this transaction.',
          422
        );
      }
      $updateBalance = Account::where('id', $request->accountId)->update(['balance' => $balance - $request->amount]);
    }

    $transaction = Transaction::create(array_merge($validator->validated(), ['account_id' => $request->accountId]));

    return $this->jsonResponse(
      array_merge([$transaction], [$balance]),
      'User transactions successfully created.',
      200
    );
  }


  /**
   * Get All Savings (Tabung) Related to the user
   * 
   * Method: GET
   * 
   * The API endpoint for getting the list of savings (tabung) of the user
   * Route: /savings/all/{userId}
   * 
   * @urlParam accountId integer required The user ID. Example: 1
   * 
   * In 200 status, the list of savings related to the user is fetched
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSavingsList(Request $request)
  {
    $savingsAll = Saving::latest()->where([['user_id', $request->userId], ['status', 'ACTIVE']])->get();

    return $this->jsonResponse(
      $savingsAll,
      'User savings list successfully fetched.',
      200
    );
  }

  /**
   * Get A Single Savings (Tabung) Related to the user
   * 
   * Method: GET
   * 
   * The API endpoint for getting a single savings (tabung) based on the savings ID
   * Route: /savings/get/{id}
   * 
   * @urlParam id integer required The savings ID. Example: 1
   * 
   * In 200 status, the a single savings information of the user is fetched
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSingleSavings(Request $request)
  {
    $singleSavings = Saving::where('id', $request->id)->first();

    return $this->jsonResponse(
      $singleSavings,
      'Single user savings successfully fetched.',
      200
    );
  }

  /**
   * Create a New Savings (Tabung)
   * 
   * Method: POST
   * 
   * The API endpoint for creating a new savings (tabung) for the user
   * Route: /savings/create/{userId}
   * 
   * @urlParam accountId integer required The user ID. Example: 1
   * 
   * In 200 status, will insert a new records of savings into the DB,
   * and return the savings (tabung) info
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function createNewSavings(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'icon' => 'nullable|string',
      'target_amount' => 'required|numeric|min:0.01',
      'due_date' => 'nullable|date',
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    $savings = Saving::create(array_merge($validator->validated(), ['user_id' => $request->userId]));

    return $this->jsonResponse(
      $savings,
      'A new savings successfully created.',
      200
    );
  }

  /**
   * Update an existing Savings (Tabung)
   * 
   * Method: PUT
   * 
   * The API endpoint for updating an existing savings (tabung) of the user
   * Route: /savings/update/{savingsId}
   * 
   * @urlParam savingsId integer required The savings ID. Example: 1
   * 
   * In 200 status, will update the records of savings into the DB,
   * and return the savings (tabung) info
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function updateSavings(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'icon' => 'nullable|string',
      'target_amount' => 'required|numeric|min:0.01',
      'due_date' => 'nullable|date',
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    // $savings = Saving::create(array_merge($validator->validated(), ['user_id' => $request->userId]));
    $savings = Saving::where('id', $request->savingsId)->update($validator->validated());

    return $this->jsonResponse(
      $savings,
      'The savings updated successfully.',
      200
    );
  }
  /**
   * Delete an existing Savings (Tabung)
   * 
   * Method: PUT
   * 
   * The API endpoint for deleting an existing savings (tabung) of the user
   * Route: /savings/delete/{savingsId}
   * 
   * @urlParam savingsId integer required The savings ID. Example: 1
   * 
   * In 200 status, the savings records will have the status set to deleted in the DB
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function deleteSavings(Request $request)
  {

    $savings = Saving::where('id', $request->savingsId)->update(array('status' => 'DELETED'));

    return $this->jsonResponse(
      $savings,
      'Successfully delete the savings record.',
      200
    );
  }

  /**
   * Get All Savings Transactions Of the Savings
   * 
   * Method: GET
   * 
   * The API endpoint for getting the list of transactions of the savings (tabung)
   * Route: /savings/getTransactions/{savingsId}
   * 
   * @urlParam savingsId integer required The savings ID. Example: 1
   * 
   * In 200 status, the list of transactions related to the savings is fetched
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSavingsTransactions(Request $request)
  {
    $savingsTransactions = SavingTransaction::latest()->where('savings_id', $request->savingsId)->get();

    return $this->jsonResponse(
      $savingsTransactions,
      'Savings transactions successfully fetched.',
      200
    );
  }

  /**
   * Create a New Savings Transaction
   * 
   * Method: POST
   * 
   * The API endpoint for creating a new savings transaction for the savings
   * Route: /savings/create-transaction/{savingsId}
   * 
   * @urlParam savingsId integer required The savings ID. Example: 1
   * 
   * In 200 status, will insert a new records of transaction of the savings into the DB,
   * and return the transaction info
   * 
   * @authenticated
   * @header Authorization Bearer {{token}}
   * @response 401 scenario = "invalid token"
   * 
   * @return \Illuminate\Http\JsonResponse
   */
  public function createSavingsTransaction(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'amount' => 'required|numeric|min:0.01',
      'process_type' => 'required',
      // 'transaction_id' => 'required'
    ]);

    if ($validator->fails()) {
      // return response()->json($validator->errors()->toJson(), 400);
      return $this->jsonResponse(
        $validator->errors(),
        'Invalid Input Parameter. Validation Failed',
        422
      );
    }

    $currentAmount = Saving::where('id', $request->savingsId)->value('current_amount');

    if ($request->process_type === 'FUND') {
      $updateCurrentAmount = Saving::where('id', $request->savingsId)->update(['current_amount' => $currentAmount + $request->amount]);
    }

    if ($request->process_type === 'WITHDRAW') {
      if ($currentAmount - $request->amount < 0) {
        return $this->jsonResponse(
          $currentAmount,
          'Your Saving Current Amount is less than the amount you are trying to withdraw.',
          422
        );
      }
      $updateCurrentAmount = Saving::where('id', $request->savingsId)->update(['current_amount' => $currentAmount - $request->amount]);
    }

    $savingTran = SavingTransaction::create(array_merge($validator->validated(), ['savings_id' => $request->savingsId]));

    return $this->jsonResponse(
      $savingTran,
      'Savings transaction successfully created.',
      200
    );
  }
}
