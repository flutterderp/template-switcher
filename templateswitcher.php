<?php
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class plgSystemTemplateSwitcher extends JPlugin
{
	protected $autoloadLanguage = true;
	
	public function onAfterInitialise()
	{
		$app			= JFactory::getApplication();
		$session	= JFactory::getSession();
		
		// Confirm we are on the front end
		if($app instanceof JApplicationSite)
		{
			require_once(JPATH_SITE . '/plugins/system/templateswitcher/mdetect/Mobile_Detect.php');
			$browser				= new Mobile_Detect();
			$jinput					= $app->input;
			$jget						= $jinput->get;
			$jget_fullsite	= $jget->get('fullsite', '', 'string');
			$full_site			= $session->get('fullsite');
			
			// Some sites may have a "full site" link somewhere on the page
			if(isset($jget_fullsite) && !empty($jget_fullsite))
			{
				$session->set('fullsite', true);
				$full_site = true;
			}
			
			/**
			 * @todo Add option to enable/disable tablets as "mobile"
			 * @todo Investigate other uses for changing to secondary template (i.e. use in-development template for certain IPs)
			 */
			if(($browser->isMobile() && !$browser->isTablet()) && $full_site !== true)
			{
				$template	= $this->params->get('template');
				$db				= JFactory::getDbo();
				$sql			= $db->getQuery(true);
				$sql
					->select('params')
					->from($db->quoteName('#__template_styles'))
					->where('template = ' . $db->quote($template));
				$db->setQuery($sql, 0, 1);
				
				try
				{
					$params = $db->loadResult();
				}
				catch(RuntimeException $e)
				{
					$params = null;
				}
				
				$app->setTemplate($template, $params);
			}
		}
		
		return true;
	}
}
