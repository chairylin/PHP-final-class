<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}

use Bitrix\Main\Loader;
Loader::IncludeModule('highloadblock');
use Bitrix\Highloadblock as HL;

if(!class_exists('Init')) {
	final class Init extends CBitrixComponent
	{
		private $config = [
			'table_name' => 'test',
		];
		private $id_table;
		private $fill_count = 1;
		private $result_status;
		private $result_filter;
		
		function __construct()
		{
			$this->create();
			$this->fill();
		}
		
		private function getEntityDataClass($HlBlockId)
        {
			if (empty($HlBlockId) || $HlBlockId < 1)
			{
				return false;
			}
			$hlblock = HL\HighloadBlockTable::getById($HlBlockId)->fetch();
			$entity = HL\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			
			return $entity_data_class;
		}
		
		private function getBlockTableList ()
		{
			return array_pop(HL\HighloadBlockTable::getList( array('filter' => array('TABLE_NAME'=>$this->config['table_name'])) )->fetchAll() );
		}
		
		private function getResultStatus()
		{
			$obEnum = new \CUserFieldEnum;
			$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_RESULT"));

			while($arEnum = $rsEnum->Fetch()){
				if( $arEnum['VALUE'] == 'normal' || $arEnum['VALUE'] == 'success'){
					$this->result_filter[] = $arEnum['ID'];
				}
				$this->result_status[] = $arEnum;
			}
		}
		
		private function createHighloadblock()
		{
			$list = $this->getBlockTableList();
				
			if ( empty($list) ) {
				$this->id_table = $this->createHLTable();
			} else {
				$this->id_table = $list["ID"];
			}
			
			$this->getResultStatus();
		}
		
		private function createHLTable()
        {
            $arLangs = [
                'ru' => 'Таблица тест',
                'en' => 'Table test'
            ];

            $result = HL\HighloadBlockTable::add([
                'NAME' => 'Test',
                'TABLE_NAME' => $this->config['table_name'],
            ]);

            if ($result->isSuccess()) {
                $id = $result->getId();
                foreach($arLangs as $lang_key => $lang_val){
                    HL\HighloadBlockLangTable::add([
                        'ID' => $id,
                        'LID' => $lang_key,
                        'NAME' => $lang_val
                    ]);
                }
            } else {
                $errors = $result->getErrorMessages();
                var_dump($errors);
            }

            $UFObject = 'HLBLOCK_'.$id;

            $arFields = [
                'UF_SCRIPT_NAME'=>[
					'FIELD' => [
						'ENTITY_ID' => $UFObject,
						'FIELD_NAME' => 'UF_SCRIPT_NAME',
						'USER_TYPE_ID' => 'string',
						'MANDATORY' => 'Y',
						"EDIT_FORM_LABEL" => ['ru'=>'Имя скрипта', 'en'=>'script name'],
						"LIST_COLUMN_LABEL" => ['ru'=>'Имя скрипта', 'en'=>'script name'],
						"LIST_FILTER_LABEL" => ['ru'=>'Имя скрипта', 'en'=>'script name'],
					],
				],
                'UF_START_TIME'=>[
					'FIELD' => [
						'ENTITY_ID' => $UFObject,
						'FIELD_NAME' => 'UF_START_TIME',
						'USER_TYPE_ID' => 'integer',
						'MANDATORY' => 'Y',
						"EDIT_FORM_LABEL" => ['ru'=>'Время начала', 'en'=>'start time'],
						"LIST_COLUMN_LABEL" => ['ru'=>'Время начала', 'en'=>'start time'],
						"LIST_FILTER_LABEL" => ['ru'=>'Время начала', 'en'=>'start time'],
					],
                ],
                'UF_END_TIME'=>[
					'FIELD' => [
						'ENTITY_ID' => $UFObject,
						'FIELD_NAME' => 'UF_END_TIME',
						'USER_TYPE_ID' => 'integer',
						'MANDATORY' => 'Y',
						"EDIT_FORM_LABEL" => ['ru'=>'Время завершения', 'en'=>'end time'],
						"LIST_COLUMN_LABEL" => ['ru'=>'Время завершения', 'en'=>'end time'],
						"LIST_FILTER_LABEL" => ['ru'=>'Время завершения', 'en'=>'end time'],
					],
                ],
				'UF_RESULT'=>[
					'FIELD' => [
						'ENTITY_ID' => $UFObject,
						'FIELD_NAME' => 'UF_RESULT',
						'USER_TYPE_ID' => 'enumeration',
						'MANDATORY' => 'Y',
						"EDIT_FORM_LABEL" => ['ru'=>'Результат', 'en'=>'result'],
						"LIST_COLUMN_LABEL" => ['ru'=>'Результат', 'en'=>'result'],
						"LIST_FILTER_LABEL" => ['ru'=>'Результат', 'en'=>'result'],
					],
					'VALUES' => [
						'n0' => [
							'VALUE' => 'normal',
							'DEF' => 'N',
						],
						'n1' => [
							'VALUE' => 'illegal',
							'DEF' => 'N',
						],
						'n2' => [
							'VALUE' => 'failed',
							'DEF' => 'N',
						],
						'n3' => [
							'VALUE' => 'success',
							'DEF' => 'N',
						],
					],
                ],
            ];

			$obUserField  = new CUserTypeEntity();
			$obEnum = new CUserFieldEnum();
			
            foreach($arFields as $arField){
                $ID = $obUserField->Add($arField['FIELD']);
				if( $arField['FIELD']['USER_TYPE_ID'] == 'enumeration' ){
					$obEnum->SetEnumValues($ID, $arField['VALUES']);
				}
            }

            return $id;
        }
		
		private function random_script_name()
		{
			$listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$path = $_SERVER['DOCUMENT_ROOT'];
			$depth = mt_rand(1,5);
			
			for ( $i=0; $i<=$depth; $i++ ) {
				$length = mt_rand(5, 10);
				$path .= '/' . substr(str_shuffle($listAlpha),0,$length);
			}
			return $path . '/' . substr(str_shuffle($listAlpha),0,$length) . '.php';
		}
		
		private function create_record()
		{
			$start_time = mt_rand(1000000000,time());
			
			return [
				'UF_SCRIPT_NAME' => $this->random_script_name(),
				'UF_START_TIME' => $start_time,
				'UF_END_TIME' => $start_time + mt_rand(5, 20),
				'UF_RESULT' => $this->result_status[mt_rand(0,3)]['ID'],
			];
		}
		
		private function create()
		{
			$this->createHighloadblock();
		}
		
		private function fill()
		{
			$entity_data_class = $this->getEntityDataClass($this->id_table);
			
			for($i=0; $i<$this->fill_count; $i++){
				$result = $entity_data_class::add($this->create_record());
			}
		}
		
		public function get()
		{
			$entity_data_class = $this->getEntityDataClass($this->id_table);
			$res = $entity_data_class::getList(
				array(
					'select' => ['*'],
					'order' => ['ID' => 'ASC'],
					'filter' => [
						'UF_RESULT' => $this->result_filter,
						],
				)
				)->fetchAll();
			
			return $res;
		}
	}
}