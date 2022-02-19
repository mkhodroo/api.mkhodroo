<?php

namespace App\Http\Controllers;

use App\Models\LivescoreCredits;
use App\Models\LiveScoreModel;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class LiveScoreController extends Controller
{
    private $model; 
    private $LSUserCont;
    private $LSCreditCont;
    private $now;
    private $now_date;

    public function __construct() {
        $this->model = new LiveScoreModel();
        $this->LSUserCont = new LivescoreUsersController();
        $this->LSCreditCont = new LivescoreUserCreditController();
        $this->now = date('Y-m-d H:i:s');
        $this->now_date = date('Y-m-d');
    }

    public function get_livescore(Request $r)
    {
        $allow = $this->user_has_credit_to_get_livescore($this->LSUserCont->get_user_by_domain($r->getHttpHost()));
        if(!$allow)
            return [
                'status' => 403,
                'message' => config('livescore')['api_msg']['credit_expire']
            ];
        $this->update_live_score($r);
        return [
            'status' => 200,
            'data' => $this->livescore_json()
        ];
        
    }

    public function user_has_credit_to_get_livescore($user)
    {
        $expire_at = Carbon::parse($user->expire_at);
        $now = Carbon::parse($this->now);
        $diff = $now->diffInSeconds($expire_at, false);
        return $diff > 0 ? true : false ;
    }

    public function livescore_json()
    {
        $rows = $this->today_matches();
        $data = [];
        $i = 0 ;
        foreach($rows as $row){
            $data[$i]['host'] = $row->host;
            $data[$i]['host_goals'] = $row->host_goals;
            $data[$i]['guest_goals'] = $row->guest_goals;
            $data[$i]['guest'] = $row->guest;
            $data[$i]['match_time'] = $row->match_time;
            $data[$i]['match_timer'] = $row->match_timer;
            $i++;
        }
        return $data;
    }

    public function today_matches()
    {
        return $this->model->where('created_at', 'like', "$this->now_date%")->get();
    }

    public function update_live_score($r)
    {
        // return $this->last_record()->updated_at->format('d M Y');
        $allow = $this->allow_to_update($this->now, $this->last_record()->updated_at);
        if(!$allow){
            return null;
        }
        $varzesh3_livescore_page = file_get_contents('https://www.varzesh3.com/livescore');
        // echo $varzesh3_livescore_page;
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $content  = mb_convert_encoding($varzesh3_livescore_page , 'HTML-ENTITIES', 'UTF-8');
        $html = $dom->loadHTML($content);
        $xpath = new DOMXPath($dom);
        $classname="matches-holder";
        $query = '//*[@class="fixture-result-match"]';
        $entries = $xpath->query($query);
    
        foreach($entries as $e){
            $data['match_time'] = $e->childNodes[1]->childNodes[1]->nodeValue ;
            $data['match_timer'] =  trim($e->childNodes[1]->childNodes[3]->nodeValue);
            $data['host'] = trim($e->childNodes[3]->childNodes[1]->nodeValue);
            $data['host_goals']= trim($e->childNodes[3]->childNodes[3]->childNodes[1]->nodeValue);
            $data['guest_goals'] = trim($e->childNodes[3]->childNodes[3]->childNodes[5]->nodeValue);
            $data['guest'] = trim($e->childNodes[3]->childNodes[5]->nodeValue);
            $data['date'] = date('Y-m-d H:i:s');
            $id = $this->find_live_match_row($data);
            if($id)
                $this->update_livescore_row($id, $data);
            else
                $this->insert_livescore_row($data);  
            // var_dump($data);
        } 
    }

    public function last_record()
    {
        return $this->model->orderBy('id', 'desc')->first();
    }

    public function find_live_match_row($data)
    {
        $date = date('Y-m-d');
        $id = $this->model->where('host', $data['host'])
                    ->where('guest', $data['guest'])
                    ->where('created_at', 'like', "$date%")
                    ->first();
        if($id)
            return $id->id;
        return false;
    }

    public function update_livescore_row($id,$data)
    {
        $row = LiveScoreModel::find($id);
        $allow = $this->allow_to_update(date('Y-m-d H:i:s'), $row->updated_at);
        if($allow){
            $row->host_goals = $data['host_goals'];
            $row->guest_goals = $data['guest_goals'];
            $row->match_time = $data['match_time'];
            $row->match_timer = $data['match_timer'];
            $row->updated_at = date('Y-m-d H:i:s');
            $row->save();
        }
    }

    public function insert_livescore_row($data)
    {
        $row = new LiveScoreModel();
        $row->host = $data['host'];
        $row->host_goals = $data['host_goals'];
        $row->guest_goals = $data['guest_goals'];
        $row->guest = $data['guest'];
        $row->match_time = $data['match_time'];
        $row->match_timer = $data['match_timer'];
        $row->save();
    }

    public function allow_to_update($now, $last_update)
    {
        $last_update = Carbon::parse($last_update);
        $now = Carbon::parse($now);
        $diff = $last_update->diffInSeconds($now, false);
        return $diff > config('livescore')['update_every'] ? true : false ;
    }

    public function pay(Request $r)
    {
        return var_dump($r->headers->get('referer'));
        $data = array("merchant_id" => config('zarinpal')['merchant_id'],
            "amount" => $r->credit,
            "callback_url" => env('APP_URL') . "api/livescore/verify",
            "description" => "خرید تست",
            "metadata" => [ "email" => "info@email.com","mobile"=>"09376922176"],
            );
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    $data['user_id'] = $this->LSUserCont->get_user_by_domain($r->getHttpHost())->id;
                    $data['credit'] = $r->credit;
                    $data['authority'] = $result['data']["authority"];
                    $this->LSCreditCont->insert($data);
                    return redirect('https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                }
            } else {
                return 'Error Code: ' . $result['errors']['code'];
                return 'message: ' .  $result['errors']['message'];

            }
        }
    }

    public function verify(Request $r)
    {
        $Authority = $_GET['Authority'];
        $credit_record = $this->LSCreditCont->get_by_authority($Authority);
        $data = array("merchant_id" => config('zarinpal')['merchant_id'], "authority" => $Authority, "amount" => $credit_record->credit);
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        if ($err) {
            $this->LSCreditCont->edit_status($credit_record, config('livescore')['status']['cancel']);
            return response(config('livescore')['transaction_msg']['error']);
        } else {
            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                $this->LSCreditCont->edit_status($credit_record, config('livescore')['status']['ok']);
                $this->LSCreditCont->insert_refId($credit_record, $result['data']['ref_id']);
                $this->LSUserCont->edit_expire(
                    $credit_record->user(), $this->LSCreditCont->get_expire_by_credit($credit_record->credit)
                );
                return response(config('livescore')['transaction_msg']['ok']. $result['data']['ref_id']);
            } else {
                $this->LSCreditCont->edit_status($credit_record, config('livescore')['status']['cancel']);
                return response(config('livescore')['transaction_msg']['cancel']);
            }
        }
    }
}
