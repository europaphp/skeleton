<?php

class Europa_Db_RecordSet extends ArrayObject
{
	public function __set($name, $value)
	{
		foreach ($this as $record) {
			$record->$name = $value;
		}
	}
	
	public function save($cascade = true)
	{
		foreach ($this as $record) {
			$record->save($cascade);
		}
	}
	
	public function delete($cascade = true)
	{
		foreach ($this as $record) {
			$record->delete($cascade);
		}
	}
}