<?php             
/*
file: Excel_model.php 匯出報表專用
*/                   
class Excel_model extends CI_Model 
{         
	function __construct()
	{
		parent::__construct(); 
		$this->load->database(); 
		
		$this->now_str = date('Y-m-d H:i:s'); 
		
		ini_set('max_execution_time','300');
		ini_set('memory_limit','512M');
		
		// 載入 excel
		$this->load->library('excel');
    }   
     
	public function init($vars)
	{
		$this->vars = $vars;
    } 
	
	// 會員名單報表
	public function export_members()
	{
		trigger_error(EXPORT_LOG_TITLE. '..start..' . __FUNCTION__);
		
		// 讀入廠站資料
		$sql = "
					select
						members.member_name as member_name,
						members.lpr as lpr,
						members.contract_no as contract_no,
						members.start_date as start_date,
						members.end_date as end_date,
						members.amt as amt,
						members.update_time as update_time,
						members.member_attr,
						members.fee_period,
						members.mobile_no,
						members.deposit,
						members.suspended,
						members.locked,
						members.valid_time
					from members
					ORDER BY update_time DESC
				";
		
		$results = $this->db->query($sql)->result_array();
		
		if(empty($results))
		{
			trigger_error(EXPORT_LOG_TITLE.'..no data..' . $this->db->last_query());
			return false;
		}
		
		//$total_count = $this->db->query($sql)->num_rows();
		
		// 產生 Excel
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$col_A_mapping = array('col_name' => 'A', 'col_title' => '會員名稱', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_B_mapping = array('col_name' => 'B', 'col_title' => '車牌號碼', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_C_mapping = array('col_name' => 'C', 'col_title' => '合約代碼', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_D_mapping = array('col_name' => 'D', 'col_title' => '開始時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_E_mapping = array('col_name' => 'E', 'col_title' => '結束時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_F_mapping = array('col_name' => 'F', 'col_title' => '租金', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_G_mapping = array('col_name' => 'G', 'col_title' => '最後更新時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_H_mapping = array('col_name' => 'H', 'col_title' => '身份', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_I_mapping = array('col_name' => 'I', 'col_title' => '繳期', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_J_mapping = array('col_name' => 'J', 'col_title' => '電話', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_K_mapping = array('col_name' => 'K', 'col_title' => '押金', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_L_mapping = array('col_name' => 'L', 'col_title' => '停權 (營管操作)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_M_mapping = array('col_name' => 'M', 'col_title' => '鎖車 (會員操作)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_N_mapping = array('col_name' => 'N', 'col_title' => '有效期限 (審核後更新)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);

		$raw_index = 1;
		$objPHPExcel->getActiveSheet()->setTitle('下載');
		$objPHPExcel->getActiveSheet()->setCellValue($col_A_mapping['col_name'].$raw_index, $col_A_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_B_mapping['col_name'].$raw_index, $col_B_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_C_mapping['col_name'].$raw_index, $col_C_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_D_mapping['col_name'].$raw_index, $col_D_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_E_mapping['col_name'].$raw_index, $col_E_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_F_mapping['col_name'].$raw_index, $col_F_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_G_mapping['col_name'].$raw_index, $col_G_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_H_mapping['col_name'].$raw_index, $col_H_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_I_mapping['col_name'].$raw_index, $col_I_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_J_mapping['col_name'].$raw_index, $col_J_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_K_mapping['col_name'].$raw_index, $col_K_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_L_mapping['col_name'].$raw_index, $col_L_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_M_mapping['col_name'].$raw_index, $col_M_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_N_mapping['col_name'].$raw_index, $col_N_mapping['col_title']);
		
		$warning_style = array(
			//'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
			'font'  => array(
		        'bold'  => true,
				'color' => array('rgb' => 'FF0000'),
		        'size'  => 16,
		        'name'  => 'Verdana'
		    )
		);
		
		$hq_info = $this->vars['mcache']->get('info'); 

		$count = 0;
		foreach($results as $rows)
		{
			$raw_index += 1;

			$member_name = $rows['member_name'];
			$lpr = $rows['lpr'];
			$contract_no = $rows['contract_no'] ? $rows['contract_no'] : '';
			$start_date = $rows['start_date'];
			$end_date = $rows['end_date'];
			$amt = $rows['amt'] ? $rows['amt'] : '0';
			$update_time = $rows['update_time'];
			$member_attr = ( empty($hq_info['member_attr']) || empty($rows['member_attr']) || empty($hq_info['member_attr'][$rows['member_attr']]) ) ? '無' : $hq_info['member_attr'][$rows['member_attr']];
			$fee_period = ( empty($hq_info['period_name']) || empty($rows['fee_period']) || empty($hq_info['period_name'][$rows['fee_period']]) ) ? '無' : $hq_info['period_name'][$rows['fee_period']];
			$mobile_no = $rows['mobile_no'];
			$deposit = $rows['deposit'];
			$suspended = (empty($rows['suspended'])) ? '無' : '已停權';
			$locked = (empty($rows['locked'])) ? '無' : '已鎖車';
			$valid_time = $rows['valid_time'];

			$count++;

			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_A_mapping['col_name'].$raw_index, $member_name, $col_A_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_B_mapping['col_name'].$raw_index, $lpr, $col_B_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_C_mapping['col_name'].$raw_index, $contract_no, $col_C_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_D_mapping['col_name'].$raw_index, $start_date, $col_D_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_E_mapping['col_name'].$raw_index, $end_date, $col_E_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_F_mapping['col_name'].$raw_index, $amt, $col_F_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_G_mapping['col_name'].$raw_index, $update_time, $col_G_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_H_mapping['col_name'].$raw_index, $member_attr, $col_H_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_I_mapping['col_name'].$raw_index, $fee_period, $col_I_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_J_mapping['col_name'].$raw_index, $mobile_no, $col_J_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_K_mapping['col_name'].$raw_index, $deposit, $col_K_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_L_mapping['col_name'].$raw_index, $suspended, $col_L_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_M_mapping['col_name'].$raw_index, $locked, $col_M_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_N_mapping['col_name'].$raw_index, $valid_time, $col_N_mapping['col_type']);
			
			// 設定 style
			if($valid_time != $end_date)
			{
				$objPHPExcel->getActiveSheet()->getStyle($col_N_mapping['col_name'].$raw_index)->applyFromArray($warning_style);	
			}
		}
		
		// 網站下載
		$filename_prefix = iconv('UTF-8', 'Big5', '會員資料 - '. STATION_NAME);
		$filename_postfix = iconv('UTF-8', 'Big5', '(現況)');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename_prefix. ' - ' . $filename_postfix . '.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		
		trigger_error(EXPORT_LOG_TITLE . '..completed..' . __FUNCTION__ . '|count:' . $count);
		
		return true;
	}
	
    
	// 進出記錄報表
	public function export_cario_data($query_year, $query_month)
	{
		ini_set('max_execution_time','300');
		ini_set('memory_limit','512M');
		
		trigger_error(EXPORT_LOG_TITLE. '..start..' . __FUNCTION__ . "|{$query_year},{$query_month}");
		
		// 讀入廠站資料
		$sql = "
				SELECT
					cario.obj_id AS plate_no,
					cario.in_time as in_time,
					cario.out_time as out_time,
					members.member_name as member_name,
					CONCAT( FLOOR(HOUR(TIMEDIFF(cario.in_time, cario.out_time)) / 24), ' 日 ',
						MOD(HOUR(TIMEDIFF(cario.in_time, cario.out_time)), 24), ' 時 ',
						MINUTE(TIMEDIFF(cario.in_time, cario.out_time)), ' 分') as time_period
				FROM cario
					left join members on cario.member_no = members.member_no
				WHERE cario.err = 0 and cario.obj_id != 'NONE'
					and YEAR(cario.in_time) = {$query_year} and MONTH(cario.in_time) = {$query_month}
					and cario.out_time is not null
				ORDER BY cario.in_time ASC
			";
		
		$results = $this->db->query($sql)->result_array();
		
		if(empty($results))
		{
			trigger_error(EXPORT_LOG_TITLE.'..no data..' . $this->db->last_query());
			return false;
		}
		
		//$total_count = $this->db->query($sql)->num_rows();
		
		// 產生 Excel
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$col_A_mapping = array('col_name' => 'A', 'col_title' => '車牌號碼', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_B_mapping = array('col_name' => 'B', 'col_title' => '進場時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_C_mapping = array('col_name' => 'C', 'col_title' => '離場日期', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_D_mapping = array('col_name' => 'D', 'col_title' => '停車時數', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_E_mapping = array('col_name' => 'E', 'col_title' => '場站名稱', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_F_mapping = array('col_name' => 'F', 'col_title' => '會員名稱', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$raw_index = 1;
		$objPHPExcel->getActiveSheet()->setTitle('下載');
		$objPHPExcel->getActiveSheet()->setCellValue($col_A_mapping['col_name'].$raw_index, $col_A_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_B_mapping['col_name'].$raw_index, $col_B_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_C_mapping['col_name'].$raw_index, $col_C_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_D_mapping['col_name'].$raw_index, $col_D_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_E_mapping['col_name'].$raw_index, $col_E_mapping['col_title']);
		$objPHPExcel->getActiveSheet()->setCellValue($col_F_mapping['col_name'].$raw_index, $col_F_mapping['col_title']);

		$count = 0;
		foreach($results as $rows)
		{
			$raw_index += 1;

			$plate_no = $rows['plate_no'];
			$in_time = $rows['in_time'];
			$out_time = $rows['out_time'];
			$time_period = $rows['time_period'];
			$member_name = $rows['member_name'] ? $rows['member_name'] : '臨停';

			$count++;

			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_A_mapping['col_name'].$raw_index, $plate_no, $col_A_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_B_mapping['col_name'].$raw_index, $in_time, $col_B_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_C_mapping['col_name'].$raw_index, $out_time, $col_C_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_D_mapping['col_name'].$raw_index, $time_period, $col_D_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_E_mapping['col_name'].$raw_index, STATION_NAME, $col_E_mapping['col_type']);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit($col_F_mapping['col_name'].$raw_index, $member_name, $col_F_mapping['col_type']); 
		}

		// 儲存檔案
		/*
		$filename_prefix = iconv('UTF-8', 'Big5', '車牌號碼進出記錄 - '. STATION_NAME);
		$filename_postfix = iconv('UTF-8', 'Big5', $query_year . '年' .$query_month.'月份');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(EXPORT_BASE.$filename_prefix.' - '.$filename_postfix.'.xlsx');
		*/
		
		// 網站下載
		$filename_prefix = iconv('UTF-8', 'Big5', '車牌號碼進出記錄 - '. STATION_NAME);
		$filename_postfix = iconv('UTF-8', 'Big5', $query_year . '年' .$query_month.'月份');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename_prefix. ' - ' . $filename_postfix . '.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		
		trigger_error(EXPORT_LOG_TITLE . '..completed..' . __FUNCTION__ . '|count:' . $count);
		
		return true;
	}
	
	// 分時統計表
	public function export_cario_summery($station_name, $query_year, $query_month, $addr='', $phone_no='')
	{
		trigger_error(EXPORT_LOG_TITLE. '..start..' . __FUNCTION__ . "|{$station_name},{$query_year},{$query_month},{$addr},{$phone_no}");

		$sql = "
				SELECT
					cario.in_time AS in_time,
					cario.out_time AS out_time,
					cario.member_no as member_no
				FROM cario
				WHERE cario.err = 0 and cario.obj_id != 'NONE'
					and cario.out_time is not null
					and
					(
						(
							YEAR(cario.in_time) = {$query_year} and MONTH(cario.in_time) = {$query_month}
						)
						OR
						(
							YEAR(cario.out_time) = {$query_year} and MONTH(cario.out_time) = {$query_month}
						)
					)
				ORDER BY cario.in_time asc
			";

		$results = $this->db->query($sql)->result_array();
		
		if(empty($results))
		{
			trigger_error(EXPORT_LOG_TITLE.'..no data..' . $this->db->last_query());
			return false;
		}

		// PART 1: 產生分時陣列
		$KEY_SHEET_INDEX = 'sheet_index';	// 所在 sheet index
		$KEY_TITLE_INDEX = 'title_index';	// 所在 title incex
		$KEY_VALUE_INDEX = 'value_index';	// 所在 value incex
		$KEY_VALUE = 'value';	// value
		$KEY_MEMBER = 1;// 1：代表會員, 0：代表臨停
		$KEY_NONE = 0;
		$results_summary = array();

		$this_day = new DateTime("{$query_year}-{$query_month}");
		$this_day_first = $this_day->format('Y-m-01');
		$this_day_last = $this_day->format('Y-m-t');
		$day_first_time = new DateTime($this_day_first. '00:00:00');
		$day_last_time = new DateTime($this_day_last. '23:59:59');

		trigger_error(EXPORT_LOG_TITLE. '..' . __FUNCTION__ . "|{$this_day_first},{$this_day_last}");

		foreach($results as $rows)
		{
			$member_no_key = $rows['member_no'] > 0 ? $KEY_MEMBER : $KEY_NONE;

			// fetch input
			$in_time = new DateTime($rows['in_time']);
			$out_time = new DateTime($rows['out_time']);
			$in_time_hour_str = $in_time->format('Y-m-d H:00:00');
			$out_time_hour_str = $out_time->format('Y-m-d H:59:59');

			// trim by limit
			$day_start = ($in_time < $day_first_time) ? $day_first_time : new DateTime($in_time_hour_str);
			$day_end = ($out_time > $day_last_time) ? $day_last_time : new DateTime($out_time_hour_str);

			// loop
			$day_period = new DatePeriod($day_start, DateInterval::createFromDateString('1 hours'), $day_end);
			foreach ($day_period as $day)
			{
				$key_lv1 = $day->format('Y-m-d');	// 日期
				$key_lv2 = $day->format('H');		// 小時
				$key_lv3 = $member_no_key;			// 身份

				$column_index_0 = 'D';
				$row_index_0 = 9;

				if(!array_key_exists($key_lv1, $results_summary))
				{
					$results_summary[$key_lv1] = array();
				}
				if(!array_key_exists($key_lv2, $results_summary[$key_lv1]))
				{
					$results_summary[$key_lv1][$key_lv2] = array();

					// 計算位置 (資料)
					for($column_index_key_lv1 = $column_index_0, $i = 0 ; $i < ((intval($day->format('d')) - 1) % 7) ; $i++)
					{
						$column_index_key_lv1++;
						$column_index_key_lv1++;
					}

					$results_summary[$key_lv1][$key_lv2][$KEY_NONE] = array();
					$results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_SHEET_INDEX] = floor(intval($day->format('d') - 1) / 7) + 1; 		// 指定 sheet index
					$results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_TITLE_INDEX] = $column_index_key_lv1.$row_index_0;					// 指定 title index

					$results_summary[$key_lv1][$key_lv2][$KEY_MEMBER] = $results_summary[$key_lv1][$key_lv2][$KEY_NONE];	// copy
				}
				if(!array_key_exists($KEY_VALUE_INDEX, $results_summary[$key_lv1][$key_lv2][$key_lv3]))
				{
					// 計算位置 (資料)
					for($column_index_key_lv1 = $column_index_0, $i = 0 ; $i < ((intval($day->format('d')) - 1) % 7) ; $i++)
					{
						$column_index_key_lv1++;
						$column_index_key_lv1++;
					}
					if($key_lv3 == $KEY_NONE)
					{
						$column_index_key_lv1++;	// 臨停
					}
					$results_summary[$key_lv1][$key_lv2][$key_lv3][$KEY_VALUE_INDEX] = $column_index_key_lv1. ($row_index_0 + intval($key_lv2) + 2);
					$results_summary[$key_lv1][$key_lv2][$key_lv3][$KEY_VALUE] = 0;	// 初始值
				}

				$results_summary[$key_lv1][$key_lv2][$key_lv3][$KEY_VALUE] += 1;
			}
		}

		// PART 2: 產生EXCEL

		// 產生 Excel
		$inputFileName = FILE_BASE . "excel/sample2.xlsx";
		trigger_error(__FUNCTION__ . "..read: {$inputFileName}.." . file_exists($inputFileName));
		
		try 
		{
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) 
		{
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$objPHPExcel->setActiveSheetIndex(0);

		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setTitle('tmp');

		$title_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
			'font'  => array(
		        'bold'  => true,
		        //'color' => array('rgb' => 'FF0000'),
		        'size'  => 20,
		        'name'  => 'Verdana'
		    )
		);
		$lpr_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);
		$basic_style = array(
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	        )
	    );

		// 標題
		$sheet_data_title = $station_name . $query_year. '年'. $query_month .'月份停車場分時統計表';
		$sheet->setCellValueExplicit('A1', $sheet_data_title, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('A1')->applyFromArray($title_style);

		// 停車場名稱
		$sheet->setCellValueExplicit('E2', $station_name, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('E2')->applyFromArray($basic_style);

		// 地址
		$sheet->setCellValueExplicit('E3', $addr, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('E3')->applyFromArray($basic_style);

		// 電話
		$sheet->setCellValueExplicit('E4', $phone_no, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('E4')->applyFromArray($basic_style);

		// 複製 sheet
		$sheet_base = $objPHPExcel->getActiveSheet();

		$sheet1 = clone $sheet_base;
		$sheet1->setTitle($query_month. '月1號至7號');
		$objPHPExcel->addSheet($sheet1);

		$sheet2 = clone $sheet_base;
		$sheet2->setTitle($query_month. '月8號至14號');
		$objPHPExcel->addSheet($sheet2);

		$sheet3 = clone $sheet_base;
		$sheet3->setTitle($query_month. '月15號至21號');
		$objPHPExcel->addSheet($sheet3);

		$sheet4 = clone $sheet_base;
		$sheet4->setTitle($query_month. '月22號至28號');
		$objPHPExcel->addSheet($sheet4);

		$sheet5 = clone $sheet_base;
		$sheet5->setTitle($query_month. '月底');
		$objPHPExcel->addSheet($sheet5);

		foreach ($results_summary as $key_lv1 => $results_summary_lv1)
		{
			$lv1_count = 0;
			foreach ($results_summary_lv1 as $key_lv2 => $results_summary_lv2)
			{
				if(empty($results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_SHEET_INDEX])) continue;

				// 設定 日期值
				if($lv1_count == 0)
				{
					$title_index = $results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_TITLE_INDEX];
					if(!empty($title_index))
					{
						$sheet_index = $results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_SHEET_INDEX];
						$objPHPExcel->setActiveSheetIndex($sheet_index);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit($title_index, $key_lv1, PHPExcel_Cell_DataType::TYPE_STRING);
					}
				}
				$lv1_count++;

				// 設定 資料值
				$member_value_index = $results_summary[$key_lv1][$key_lv2][$KEY_MEMBER][$KEY_VALUE_INDEX];
				$member_value = $results_summary[$key_lv1][$key_lv2][$KEY_MEMBER][$KEY_VALUE];
				if(!empty($member_value_index))
				{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($member_value_index, $member_value, PHPExcel_Cell_DataType::TYPE_STRING);
				}

				$none_value_index = $results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_VALUE_INDEX];
				$none_value = $results_summary[$key_lv1][$key_lv2][$KEY_NONE][$KEY_VALUE];
				if(!empty($none_value_index))
				{
					$objPHPExcel->getActiveSheet()->setCellValueExplicit($none_value_index, $none_value, PHPExcel_Cell_DataType::TYPE_STRING);
				}
			}
		}

		// 儲存檔案
		$filename_prefix = iconv('UTF-8', 'Big5', '分時統計表 - '. $station_name);
		$filename_postfix = iconv('UTF-8', 'Big5', $query_year . '年' .$query_month.'月份');
		
		$sheetCount = $objPHPExcel->getSheetCount();
		$objPHPExcel->removeSheetByIndex(0);
		/*
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(EXPORT_BASE.$filename_prefix.' - '.$filename_postfix.'.xlsx');
		
		trigger_error(EXPORT_LOG_TITLE . '..' . __FUNCTION__ . '..completed..');
		return true;
		*/
		// 網站下載
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename_prefix. ' - ' . $filename_postfix . '.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		return true;
	}
	
	// 進出記錄表
	public function export_cario_report($station_name, $query_year, $query_month, $addr='', $phone_no='', $member_attr=0)
	{
		trigger_error(EXPORT_LOG_TITLE. '..start..' . __FUNCTION__ . "|{$station_name},{$query_year},{$query_month},{$addr},{$phone_no}");

		$where_member_attr = !empty($member_attr) ? " and members.member_attr = {$member_attr} " : "";
		
		$sql = "
			SELECT
				cario.obj_id AS plate_no,
				cario.in_time as in_time,
				cario.out_time as out_time,
				members.member_name as member_name,
				CONCAT( FLOOR(HOUR(TIMEDIFF(cario.in_time, cario.out_time)) / 24), ' 日 ',
					MOD(HOUR(TIMEDIFF(cario.in_time, cario.out_time)), 24), ' 時 ',
					MINUTE(TIMEDIFF(cario.in_time, cario.out_time)), ' 分') as time_period
			FROM cario
				left join members on cario.obj_id = members.lpr
			WHERE cario.err = 0 and cario.obj_id != 'NONE'
				and YEAR(cario.in_time) = {$query_year} and MONTH(cario.in_time) = {$query_month}
				and cario.out_time is not null
				{$where_member_attr}
			ORDER BY cario.in_time ASC
		";
		
		$results = $this->db->query($sql)->result_array();
		
		if(empty($results))
		{
			trigger_error(EXPORT_LOG_TITLE.'..no data..' . $this->db->last_query());
			return false;
		}

		// PART 2: 產生EXCEL

		// 產生 Excel
		$inputFileName = FILE_BASE . "excel/sample1.xlsx";
		trigger_error(__FUNCTION__ . "..read: {$inputFileName}.." . file_exists($inputFileName));
		
		try 
		{
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) 
		{
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$objPHPExcel->setActiveSheetIndex(0);
		$col_A0_mapping = array('col_name' => 'A', 'col_title' => '', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_A_mapping = array('col_name' => 'B', 'col_title' => '車號', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_B_mapping = array('col_name' => 'C', 'col_title' => '進場時間(A)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_C_mapping = array('col_name' => 'E', 'col_title' => '出場時間(B)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_D_mapping = array('col_name' => 'G', 'col_title' => '停車時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);

		$col_E0_mapping = array('col_name' => 'I', 'col_title' => '', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_E_mapping = array('col_name' => 'J', 'col_title' => '車號', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_F_mapping = array('col_name' => 'K', 'col_title' => '進場時間(A)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_G_mapping = array('col_name' => 'M', 'col_title' => '出場時間(B)', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);
		$col_H_mapping = array('col_name' => 'O', 'col_title' => '停車時間', 'col_type' => PHPExcel_Cell_DataType::TYPE_STRING);

		$raw_index = 10;
		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setTitle('下載');

		$title_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
			'font'  => array(
		        'bold'  => true,
		        //'color' => array('rgb' => 'FF0000'),
		        'size'  => 20,
		        'name'  => 'Verdana'
		    )
		);
		$lpr_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			)
		);
		$basic_style = array(
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	        )
	    );

		// 標題
		$sheet_data_title = $station_name . $query_year. '年'. $query_month .'月份停車場延時統計表';
		$sheet->setCellValueExplicit('A1', $sheet_data_title, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('A1')->applyFromArray($title_style);

		// 停車場名稱
		$sheet->setCellValueExplicit('D2', $station_name, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('D2')->applyFromArray($basic_style);

		// 地址
		$sheet->setCellValueExplicit('D3', $addr, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('D3')->applyFromArray($basic_style);

		// 電話
		$sheet->setCellValueExplicit('D4', $phone_no, PHPExcel_Cell_DataType::TYPE_STRING);
		$sheet->getStyle('D4')->applyFromArray($basic_style);

		$count = 0;
		foreach($results as $rows)
		{
			$member_name = $rows['member_name'];
			$plate_no = $rows['plate_no'];
			$in_time = $rows['in_time'];
			$out_time = empty($rows['out_time']) ? '無離場記錄或車辨失敗' : $rows['out_time'];
			$time_period = $rows['time_period'];

			if($count % 2 == 0)
			{
				$raw_index += 1;
				$sheet->setCellValueExplicit($col_A0_mapping['col_name'].$raw_index, $member_name, $col_A0_mapping['col_type']);
				$sheet->setCellValueExplicit($col_A_mapping['col_name'].$raw_index, $plate_no, $col_A_mapping['col_type']);
				$sheet->setCellValueExplicit($col_B_mapping['col_name'].$raw_index, $in_time, $col_B_mapping['col_type']);
				$sheet->setCellValueExplicit($col_C_mapping['col_name'].$raw_index, $out_time, $col_C_mapping['col_type']);
				$sheet->setCellValueExplicit($col_D_mapping['col_name'].$raw_index, $time_period, $col_D_mapping['col_type']);
				//$sheet->getStyle("A1:B1")->applyFromArray($style);

				$sheet->getStyle($col_A0_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_A_mapping['col_name'].$raw_index)->applyFromArray($lpr_style);
				$sheet->getStyle($col_B_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_C_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_D_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
			}
			else
			{
				$sheet->setCellValueExplicit($col_E0_mapping['col_name'].$raw_index, $member_name, $col_E0_mapping['col_type']);
				$sheet->setCellValueExplicit($col_E_mapping['col_name'].$raw_index, $plate_no, $col_E_mapping['col_type']);
				$sheet->setCellValueExplicit($col_F_mapping['col_name'].$raw_index, $in_time, $col_F_mapping['col_type']);
				$sheet->setCellValueExplicit($col_G_mapping['col_name'].$raw_index, $out_time, $col_G_mapping['col_type']);
				$sheet->setCellValueExplicit($col_H_mapping['col_name'].$raw_index, $time_period, $col_H_mapping['col_type']);

				$sheet->getStyle($col_E0_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_E_mapping['col_name'].$raw_index)->applyFromArray($lpr_style);
				$sheet->getStyle($col_F_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_G_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
				$sheet->getStyle($col_H_mapping['col_name'].$raw_index)->applyFromArray($basic_style);
			}

			$count++;
		}
		
		// 儲存檔案
		$filename_prefix = iconv('UTF-8', 'Big5', '車牌號碼進出記錄 - '. $station_name);
		$filename_postfix = iconv('UTF-8', 'Big5', $query_year . '年' .$query_month.'月份');
		
		$sheetCount = $objPHPExcel->getSheetCount();
		$objPHPExcel->removeSheetByIndex($sheetCount - 1);

		/*
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(EXPORT_BASE.$filename_prefix.' - '.$filename_postfix.'.xlsx');
		
		trigger_error(EXPORT_LOG_TITLE . '..' . __FUNCTION__ . '..completed..' . "count: {$count}");
		return true;
		*/
		
		// 網站下載
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename_prefix. ' - ' . $filename_postfix . '.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		return true;
	}
	
}
