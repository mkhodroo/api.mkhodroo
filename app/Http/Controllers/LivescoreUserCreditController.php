<?php

namespace App\Http\Controllers;

use App\Models\LivescoreCredits;
use App\Models\LivescoreUsersModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LivescoreUserCreditController extends Controller
{
    private $model;
    private $LSUCont;

    public function __construct() {
        $this->model = new LivescoreCredits();
        $this->LSUCont = new LivescoreUsersController();
    }

    public function insert($data)
    {
        $insert = new LivescoreCredits();
        $insert->user_id = $data['user_id'];
        $insert->credit = $data['credit'];
        $insert->authority = $data['authority'];
        $insert->status = 'pending';
        $insert->save();
        return $insert->id;
    }

    public function get_by_authority($authority)
    {
        return $this->model->where('authority', $authority)->first();
    }

    public function edit_status($credit_record, $status)
    {
        $credit_record->status = $status;
        $credit_record->save();
    }

    public function insert_refId($credit_record, $refId)
    {
        $credit_record->refId = $refId;
        $credit_record->save();
    }

    public function get_expire_by_credit($credit)
    {
        return Carbon::now()->addMonth((int)$credit / config('livescore')['credit_per_month']);
    }

    public function credit_per_month()
    {
        return config('livescore')['credit_per_month'];
    }

    public function expire_at(Request $r)
    {
        return $this->LSUCont->get_user_by_domain($r->headers->get('referer'))->expire_at;
    }
}
