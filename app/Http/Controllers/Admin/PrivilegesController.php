<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Config;

use App\Privilege;
use App\Http\Services\Log\SystemLog;

class PrivilegesController extends Controller {
    private $TABLE_NAME = "PRIVILEGES";

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $privileges = Privilege::orderBy('id', 'ASC')->get();
        $result = array("privileges" => json_decode(json_encode($privileges), true));
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {

        $input = $request->all();

        // Define rules
        $rules = [
            'txt' => 'required|max:50',
        ];

        //Define messages
        $messages = [
            'txt.required' => 'The privilege name field can not be blank.',
            'txt.max' => "The privilege name's length can't be over :max characters.",
        ];

        $validator = \Validator::make( $input, $rules, $messages );

        if ($validator->fails()) {
            return response()->json([ 'code' => 'validation', 'errors' => $validator->errors()->all() ], 200);
        } else {
            try {
                $result = Privilege::create($request->all());
                SystemLog::write(Config::get('app.admin.logInsert'), $this->TABLE_NAME . ' [ID] ' . $result->id);
                return response()->json([ 'privileges' => $result ], 200);
            } catch (Exception $e) {
                return response()->json([ 'code' => 'exception', 'errors' => $e->getMessage(), 'status' => $e->getCode() ], 200);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        // find a record from categories by id
        $privilegeUpdate  = Privilege::findOrFail($id);
        $input = $request->all();

        // Define rules
        $rules = [
            'txt' => 'required|max:50',
        ];

        //Define messages
        $messages = [
            'txt.required' => 'The privilege name field can not be blank.',
            'txt.max' => "The privilege name's length can't be over :max characters.",
        ];

        $validator = \Validator::make( $input, $rules, $messages );

        if ($validator->fails()) {
            return response()->json([ 'code' => 'validation', 'errors' => $validator->errors()->all() ], 200);
        } else {
            try {
                $detail = SystemLog::createLogForUpdatedFields($privilegeUpdate, $input, null); 
                $privileges = $privilegeUpdate->fill($input)->save();
                SystemLog::write(Config::get('app.admin.logUpdate'), $this->TABLE_NAME . ' [ID] ' . $id . ' [DETAIL] ' . $detail);
                return response()->json([ 'privileges' => $privileges ], 200);
            } catch (Exception $e) {
                return response()->json([ 'code' => 'exception', 'errors' => $e->getMessage(), 'status' => $e->getCode() ], 200);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        try {
            Privilege::find($id)->delete();
            SystemLog::write(Config::get('app.admin.logDelete'), $this->TABLE_NAME . ' [ID] ' . $id);
            return response()->json([ 'message' => 'DELETED!' ], 200);
        } catch (Exception $e) {
            return response()->json([ 'code' => 'exception', 'errors' => $e->getMessage(), 'status' => $e->getCode() ], 200);
        }
    }
}
