<?php

/**
 * Settings LangManagement index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_LangManagement_Index_View extends Settings_Vtiger_Index_View
{

	/**
	 * Process function
	 * @param App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('LANGS', App\Language::getAll());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.Settings.$moduleName.resources.LangManagement",
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs/js/dataTables.bootstrap.js',
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/Flot/jquery.flot.js',
			'~libraries/Flot/jquery.flot.stack.js',
			'~libraries/flot-valuelabels/jquery.flot.valuelabels.js',
		];
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param \App\Request $request
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/datatables.net-bs/css/dataTables.bootstrap.css',
			'modules.Settings.LangManagement.LangManagement',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
