<?php
require_once 'Altob.Payment.Core.php';

abstract class PricePlanValueKey
{
  const p0 = "p0";                        // 費用 0
  const limit0 = "limit0";                        // 上限 0
  const limit0_start_time = "limit0_start_time";  // 上限 0 開始時間
  const limit0_end_time = "limit0_end_time";      // 上限 0 結束時間
  const free0_min = "free0_min";            // 免費時間 (分)
}

/**
 * 明細拆算: 費用明細 key
 */

abstract class PriceDeatilStatusValue
{
  const TYPE_0 = 0; // 一般
  const TYPE_1 = 1; // 達上限
}

/**
 * 明細拆算: 費用明細 key
 */
abstract class PriceDeatilKey
{
  const r_no = "r_no";  // 明細拆算: 序號
  const day = "day";    // 明細拆算: 日期
  const hours = "h";    // 明細拆算: 時
  const mins = "i";     // 明細拆算: 分
  const price = "p";    // 明細拆算: 費用
  const meta = "meta";  // 明細拆算: 費用說明
  const status = "status";        // 明細拆算: 狀態
  const p0 = "p0";                          // 費用 0
  const limit0 = "limit0";                        // 上限 0
  const limit0_start_time = "limit0_start_time";  // 上限 0 開始時間
  const limit0_end_time = "limit0_end_time";      // 上限 0 結束時間
  const free0_min = "free0_min";            // 免費時間 (分)
}

/**
 * 適用: 臨停
 *
 * @version 1.0
 * @author mip
 */
class AltobPayment extends AltobPaymentAbstractClass
{
  // 取得帳單與明細
  public function getBill($inTime, $balanceTime, $stationNo)
  {
    $dateBegin = new DateTime($inTime);
    $dateEnd = new DateTime($balanceTime);
    $interval = $dateBegin->diff($dateEnd); // y, m, d, h, i, s

    // A. 取得費率設定
    $this->PricePlan = $this->getPricePlan($stationNo); // get price plan

    /*
    規則:
      1. 每小時20元，每日最高150元，前30分鐘免費
      2. 另外停車1小時候未滿30分以10元計，超過30分以20元計

      ex. 15日18:00進場~16日20:30出場:15日金額120元 + 16日金額150元 =270
      ex. 15日12:00進場~15日16:25出場:80元+10元
    */
    $p0 = $this->PricePlan[PricePlanValueKey::p0];                        			// 費用 0 (10元 /半小時)
    $limit0 = $this->PricePlan[PricePlanValueKey::limit0];                        	// 上限 0 (150元 /D)
    $limit0_start_time = $this->PricePlan[PricePlanValueKey::limit0_start_time];  	// 上限 0 開始時間 (00:00:00)
    $limit0_end_time = $this->PricePlan[PricePlanValueKey::limit0_end_time];      	// 上限 0 結束時間 (23:59:59)
    $free0_min = $this->PricePlan[PricePlanValueKey::free0_min];                  	// 免費 0 (前 30 分)

    // B. 取得特殊日期
    //$this->PricePlanDate = $this->getPricePlanDate($inTime, $balanceTime);

    // C. 開始建立帳單明細 $this->PriceDetail
    $this->PriceDetail = array();

    // D. 取得帳單結算價錢

    // D.1 前 30 分鐘免費
    if($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i < $free0_min){
      $this->Price = 0;
      return $this->genBillResult($interval);
    }

    // D.2 依照費率將時間切割, 暫存 $day_array
    $day_array = array();
    $dateFront = $dateBegin;
    $dateTail = $dateEnd;
    $period_of_basic_repeat = new DatePeriod($dateFront, DateInterval::createFromDateString('30 minutes'), $dateTail);
    foreach ($period_of_basic_repeat as $basic_part){
      $today_key = $this->genLv1Key($basic_part);
      if(!array_key_exists($today_key, $this->PriceDetail)){
        $today_data = array();
        $today_data[PriceDeatilKey::p0] = $p0;
        $today_data[PriceDeatilKey::limit0] = $limit0;
        $today_data[PriceDeatilKey::limit0_start_time] = $limit0_start_time;
        $today_data[PriceDeatilKey::limit0_end_time] = $limit0_end_time;
        $today_data[PriceDeatilKey::free0_min] = $free0_min;
        $this->PriceDetail[$today_key] = $today_data;
      }
      $this->genLv2Value(0, $today_key, $basic_part, 0, "init");
      array_push($day_array, $basic_part);
    }
    $this->PriceDetail["day_array"] = $day_array;
    //return $this->genBillResult($interval);

    // 解析 $day_array 建立帳單明細於 $this->PriceDetail
    $total_price = 0;
    $today_limit0_amt = 0;
    $count_period = count($day_array);
    for($i = 0 ; $i < $count_period ; $i+=1){
      $price_this = 0;
      $day_this = $day_array[$i];
      $today_key = $this->genLv1Key($day_this);
      $today_lv2_key =  $this->genLv2Key($day_this);
      $today_p0 = $this->PriceDetail[$today_key][PriceDeatilKey::p0];
      $today_limit0 = $this->PriceDetail[$today_key][PriceDeatilKey::limit0];
      $today_limit0_start_time = $this->PriceDetail[$today_key][PriceDeatilKey::limit0_start_time];
      $today_limit0_end_time = $this->PriceDetail[$today_key][PriceDeatilKey::limit0_end_time];
      $today_free0_min = $this->PriceDetail[$today_key][PriceDeatilKey::free0_min];

      // 重置每日收費上限
      $current_h = $day_this->format('H');
      if($current_h == substr($today_limit0_start_time, 0, 2)){
        $today_limit0_amt = 0;
      }

      // 記算金額
      $status = PriceDeatilStatusValue::TYPE_0;
      if($today_limit0_amt >= $today_limit0){
        // 已達上限值, 免費
        $price_this = 0;
        $memo = "B.4 ..FREE..";
        $status = PriceDeatilStatusValue::TYPE_1;
      }else{
        // 未達上限值, 取得時段費用
        $price_this = $this->getPrice($today_p0);

        if(array_key_exists($i - 1, $day_array)){
          if(array_key_exists($i + 1, $day_array)){
            if($today_limit0_amt + $price_this >= $today_limit0){
              // 未達上限值, 有上一筆, 有下一筆, 本次達上限
              $price_this = $today_limit0 - $today_limit0_amt;
              $today_limit0_amt = $today_limit0;
              $memo = "B.3 ..today limit : {$today_limit0}..";
              $status = PriceDeatilStatusValue::TYPE_1;
            }else{
              // 未達上限值, 有上一筆, 有下一筆, 本次未達上限
              $today_limit0_amt += $price_this;
              $memo = "B.2 ..next..";
            }
          }else{
            // 未達上限值, 有上一筆, 最後一筆
            if($today_limit0_amt + $price_this >= $today_limit0){
              // 未達上限值, 有上一筆, 最後一筆, 本次達上限
              $price_this = $today_limit0 - $today_limit0_amt;
              $today_limit0_amt = $today_limit0;
              $memo = "D.2 ..today limit : {$today_limit0}..";
              $status = PriceDeatilStatusValue::TYPE_1;
            }else{
              // 未達上限值, 有上一筆, 最後一筆, 本次未達上限
              $today_limit0_amt += $price_this;
              $memo = "D.1 ..next..";
            }
          }
        }else{
          if(array_key_exists($i + 1, $day_array)){
            // 未達上限值, 第一筆, 有下一筆
            $today_limit0_amt += $price_this;
            $memo = "B.1 ..START..a";
          }else{
            // 未達上限值, 第一筆, 最後一筆
            $memo = "C ..done..";
          }
        }
      }

      // 建立明細資料
      if(array_key_exists($i - 1, $day_array)){
        if(array_key_exists($i + 1, $day_array)){
          // 有上一筆, 有下一筆
          $this->setLv2Value($i, $today_key, $today_lv2_key, $price_this, $memo, $status);
        }else{
          // 有上一筆, 最後一筆
          $this->setLv2Value($i, $today_key, $today_lv2_key, $price_this, $memo, $status);
          $price_end = 0;
          $status = PriceDeatilStatusValue::TYPE_0;
          /*
          if($day_this->diff($dateEnd)->i < $today_free0_min){
            // 未滿 30 分, 不計
            $memo = "E.3 ..end..";
          }else{
          */
          $price_end = $this->getPrice($today_p0);;
          if($today_limit0_amt + $price_end >= $today_limit0){
            // 達上限
            $price_end = $today_limit0 - $today_limit0_amt;
            $memo = "E.2 ..today limit : {$today_limit0}..";
            $status = PriceDeatilStatusValue::TYPE_1;
          }else{
            $memo = "E.1 ..end..";
          }

          $this->genLv2Value($i + 1, $today_key, $dateEnd, $price_end, $memo, $status);
        }
      }else{
        if(array_key_exists($i + 1, $day_array)){
          // 第一筆, 有下一筆
          // do nothing
        }else{
          // 第一筆, 最後一筆
          $this->genLv2Value($i + 1, $today_key, $dateEnd, $price_this, $memo, $status);
        }
      }

      // 總結算
      $total_price += $price_this;
    }

    $this->Price = $total_price;
    return $this->genBillResult($interval);
  }

  // 取得目前時段價格
  private function getPrice($today_p0)
  {
    return $today_p0;
  }

  // 建立 lv2
  private function genLv2Info($r_no, $today_lv1_key, $today_lv2_key, $price, $price_type, $meta)
  {
    $lv1Key = $today_lv1_key;
    $lv2Key = $today_lv2_key;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::r_no] = $r_no;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::price] = $price;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::p_type] = $price_type;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::meta] = $meta;
  }

  // 建立 lv2 value
  private function genLv2Value($r_no, $lv1Key, $day, $price, $meta, $status=PriceDeatilStatusValue::TYPE_0)
  {
    $lv2Key = $this->genLv2Key($day);
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::day] = $day->format('Y-m-d H:i:s');
    $this->setLv2Value($r_no, $lv1Key, $lv2Key, $price, $meta, $status);
  }

  // 設定 lv2 value
  private function setLv2Value($r_no, $lv1Key, $lv2Key, $price, $meta, $status=PriceDeatilStatusValue::TYPE_0)
  {
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::r_no] = $r_no;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::price] = $price;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::meta] = $meta;
    $this->PriceDetail[$lv1Key][$lv2Key][PriceDeatilKey::status] = $status;
  }

}
