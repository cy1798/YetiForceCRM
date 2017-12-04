<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Currency_UIType extends Vtiger_Base_UIType
{

	protected $edit = false;

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($this->get('field')->get('uitype') === 72) {
			return self::convertToDBFormat($value, null, true);
		} else {
			return self::convertToDBFormat($value);
		}
	}

	/**
	 * Verification of data
	 * @param string $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$currentUser = \App\User::getCurrentUserModel();
			$value = str_replace($currentUser->getDetail('currency_grouping_separator'), '', $value);
			$value = str_replace($currentUser->getDetail('currency_decimal_separator'), '.', $value);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$uiType = $this->get('field')->get('uitype');
		if ($value) {
			if ($uiType === 72) {
				// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
				$value = CurrencyField::convertToUserFormat($value, null, true);
			} else {
				$value = CurrencyField::convertToUserFormat($value);
			}
			if (!$this->edit) {
				$value = $this->getDetailViewDisplayValue($value, $record, $uiType);
			}
			return \App\Purifier::encodeHtml($value);
		}
		return 0;
	}

	/**
	 * Function to get the edit value in display view
	 * @param mixed $value
	 * @param Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			$this->edit = true;
			return $this->getDisplayValue($value);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function that converts the Number into Users Currency along with currency symbol
	 * @param int|string $value
	 * @param int $recordId
	 * @param int $uiType
	 * @return Formatted Currency
	 */
	public function getDetailViewDisplayValue($value, $recordId, $uiType)
	{
		if ($uiType === 72 && $recordId) {
			$moduleName = $this->get('field')->getModuleName();
			if (!$moduleName) {
				$moduleName = \App\Record::getType($recordId);
			}
			$currencyId = \App\Fields\Currency::getCurrencyByModule($recordId, $moduleName);
			$currencySymbol = \vtlib\Functions::getCurrencySymbolandRate($currencyId)['symbol'];
		} else {
			$currencyModal = new CurrencyField($value);
			$currencyModal->initialize();
			$currencySymbol = $currencyModal->currencySymbol;
		}
		return CurrencyField::appendCurrencySymbol($value, $currencySymbol);
	}

	/**
	 * Function to transform display value for currency field
	 * @param $value
	 * @param Current User
	 * @param boolean Skip Conversion
	 * @return converted user format value
	 */
	public static function transformDisplayValue($value, $user = null, $skipConversion = false)
	{
		return CurrencyField::convertToUserFormat($value, $user, $skipConversion);
	}

	/**
	 * Function converts User currency format to database format
	 * @param <Object> $value - Currency value
	 * @param <User Object> $user
	 * @param boolean $skipConversion
	 */
	public static function convertToDBFormat($value, $user = null, $skipConversion = false)
	{
		return CurrencyField::convertToDBFormat($value, $user, $skipConversion);
	}

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Currency.tpl';
	}
}
