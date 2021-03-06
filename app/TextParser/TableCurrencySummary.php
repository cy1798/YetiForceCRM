<?php

namespace App\TextParser;

/**
 * Table currency summary class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class TableCurrencySummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_CURRENCY_SUMMARY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$html = '';
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		$columns = $inventoryField->getColumns();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		if (in_array('currency', $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] !== null) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
		}
		$html .= '<style>' .
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;}' .
			'</style>';
		if (count($fields[0]) != 0) {
			$taxes = 0;
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$taxes = $inventoryField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
			}
			if (in_array('tax', $columns) && in_array('taxmode', $columns) && in_array('currency', $columns) && $baseCurrency['id'] != $currency) {
				$RATE = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
				$html .= '<table class="productTable colapseBorder">
								<thead>
									<tr>

										<th colspan="2" class="tBorder noBottomBorder tHeader">
											<strong>' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . '</strong>
										</th>
									</tr>
								</thead>
								<tbody>';
				$currencyAmount = 0;
				foreach ($taxes as $key => &$tax) {
					$currencyAmount += $tax;

					$html .= '<tr>
									<td class="textAlignRight tBorder" width="70px">' . $key . '%</td>
									<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
				}
				$html .= '<tr>
								<td class="textAlignRight tBorder" width="80px">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
							</tr>
						</tbody>
					</table>';
			}
		}
		return $html;
	}
}
