<?php

/**
 * 結算: 收費回傳物件 key
 */
abstract class BillResultKey
{
  const days = "d";   // 結算: 日
  const hours = "h";  // 結算: 時
  const mins = "i";   // 結算: 分
  const price = "p";  // 結算: 費用
  const price_detail = "p_detail";    // 結算: 明細
  const price_plan = "p_plan";        // 結算: 費率
  const price_plan_date = "p_plan_d"; // 結算: 費率日期
}

/**
 * 收費規則 key
 */
 abstract class PricePlanKey
 {
   const price_plan = "price_plan"; // 費率
 }

/**
 * 收費規則 value
 */
abstract class PricePlanValue
{
  const TYPE_0 = 0; // 臨停
}

/**
 * 日期規則 key
 */
abstract class DatePlanKey
{
  const p_type = "p_type";  // 收費類型 (0:平日, 1:假日, etc..)
  const p_date = "p_date";      // 日期
}

/**
 * 日期規則 value
 */
abstract class DatePlanValue
{
  const TYPE_0 = 0; // 平日
  const TYPE_1 = 1; // 假日
}

/**
 * Altob 金流抽象介面
 *
 * @version 1.0
 * @author mip
 */
abstract class AltobPaymentAbstractClass
{
  public $ServiceURL = "ServiceURL";

  protected $Price = "Price";                 // 結算價錢
  protected $PriceDetail = "PriceDetail";     // 結算明細
  protected $PricePlan = "PricePlan";         // 費率設定
  protected $PricePlanDate = "PricePlanDate"; // 特殊日期

  abstract public function getBill($inTime, $balanceTime, $station); // 拿帳單 (實作這一段)

  /**
  * 產生帳單回傳結果 (最上層的資料結構)
  */
  protected function genBillResult($interval)
  {
    $data = array();
    $data[BillResultKey::days] = $interval->d;
    $data[BillResultKey::hours] = $interval->h;
    $data[BillResultKey::mins] = $interval->i;
    $data[BillResultKey::price] = $this->Price;
    $data[BillResultKey::price_detail] = $this->PriceDetail;
    $data[BillResultKey::price_plan] = $this->PricePlan;
    $data[BillResultKey::price_plan_date] = $this->PricePlanDate;
    return $data;
  }

    /**
    * 取得費率資訊
    */
    protected function getPricePlan($stationNo, $txType=PricePlanValue::TYPE_0)
    {
      $plan = array();

      // 從 DB 取得費率設定
      $result = $this->ServerPost("{$this->ServiceURL}/get_price_plan/{$stationNo}/{$txType}");
      $decode_result = json_decode($result, true);

      if(! empty($decode_result[0])){
        $plan = json_decode($decode_result[0][PricePlanKey::price_plan], true);
      }

      return $plan;
    }

    /**
    * 取得特殊日期陣列
    */
    protected function getPricePlanDate($inTime, $balanceTime)
    {
      $result = array();
      $date_plan = $this->getDatePlan($inTime, $balanceTime); // 取得特殊日期
      foreach ($date_plan as $val){
        $day = new DateTime($val[DatePlanKey::p_date]);
        $day_key = $this->genLv1Key($day);
        $result[$day_key] = $val;
      }
      return $result;
    }

    // 取得特殊日期
    private function getDatePlan($inTime, $balanceTime)
    {
      $result = array();
      $inTimestamp = strtotime($inTime);
      $balanceTimestamp = strtotime($balanceTime);
      // 算出週六日
      $weekdays = $this->get_weekdays($inTimestamp, $balanceTimestamp);
      foreach ($weekdays as $val){
        $weekday = array();
        $weekday[DatePlanKey::p_type] = DatePlanValue::TYPE_1;
        $weekday[DatePlanKey::p_date] = date('Y-m-d H:i:s', $val);
        array_push($result, $weekday);
      }
      // 從 DB 取得其它假日
      $db_result = $this->ServerPost("{$this->ServiceURL}/get_date_plan/{$inTimestamp}/{$balanceTimestamp}");
      $decode_db_result = json_decode($db_result, true);

      if(! empty($decode_db_result)){
        foreach ($decode_db_result as $val){
          $holiday = array();
          $holiday[DatePlanKey::p_type] = $val[DatePlanKey::p_type];
          $holiday[DatePlanKey::p_date] = $val[DatePlanKey::p_date]; //date('Y-m-d H:i:s', );
          array_push($result, $holiday);
        }
      }
      return $result;
    }

    // 取得指定期間週末 (timestamp)
    private function get_weekdays($from, $to=false)
    {
      if ($to == false)
        $to = $this->last_day_of_month($from);

      $days = array();

      for ($x = $from; $x < $to; $x+=86400 ) { // 60*60*24 一天的秒數
        //if (date('w', $x) > 0 && date('w', $x) < 6)
        if (date('w', $x) == 0 || date('w', $x) == 6)
          $days[] = $x;
      }
      return $days;
    }

    // 取得當月最後一天 (timestamp)
    private function last_day_of_month($ts=false)
    {
      $m = date('m', $ts);
      $y = date('y', $ts);
      return mktime(23, 59, 59, ($m+1), 0, $y);
    }

    // 呼叫其它服務
    private function ServerPost($url, $parameters=array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        $rs = curl_exec($ch);

        curl_close($ch);

        return $rs;
    }

    // 產生日期當 lv1 key
    protected function genLv1Key($date)
    {
      return $date->format('Y-m-d');
    }

    // 產生時間當 lv2 key
    protected function genLv2Key($date)
    {
      return $date->format('H:i:s');
    }

}
