<?php
/**
 * kreXX: Krumo eXXtended
 *
 * kreXX is a debugging tool, which displays structured information
 * about any PHP object. It is a nice replacement for print_r() or var_dump()
 * which are used by a lot of PHP developers.
 *
 * kreXX is a fork of Krumo, which was originally written by:
 * Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
 *
 * @author
 *   brainworXX GmbH <info@brainworxx.de>
 *
 * @license
 *   http://opensource.org/licenses/LGPL-2.1
 *
 *   GNU Lesser General Public License Version 2.1
 *
 *   kreXX Copyright (C) 2014-2017 Brainworxx GmbH
 *
 *   This library is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation; either version 2.1 of the License, or (at
 *   your option) any later version.
 *   This library is distributed in the hope that it will be useful, but WITHOUT
 *   ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 *   FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 *   for more details.
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this library; if not, write to the Free Software Foundation,
 *   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

namespace Brainworxx\M2krexx\Block\Adminhtml\Config;

use Magento\Backend\Block\Template;

/**
 * Add the form data for the cofig file editor to the template.
 *
 * @package Brainworxx\M2krexx\Block\Adminhtml\Config
 */
class Edit extends Template
{
    /**
     * {@inheritdoc}
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        $help = array();
        $settings = array();
        $factory = array();
        $pool = \Krexx::$pool;

        // Initialzing help data for the template.
        $help['skin'] = $this->prepareHelp($pool->messages->getHelp('skin'));
        $help['iprange'] = __('List of IPs that can trigger kreXX. Wildcards can be used.');
        $help['maxfiles'] = $this->prepareHelp($pool->messages->getHelp('maxfiles'));
        $help['destination'] = $this->prepareHelp($pool->messages->getHelp('destination'));
        $help['maxCall'] = $this->prepareHelp($pool->messages->getHelp('maxCall'));
        $help['disabled'] = __('Here you can disable kreXX without uninstalling the whole module.');
        $help['detectAjax'] = $this->prepareHelp($pool->messages->getHelp('detectAjax'));
        $help['analyseProtected'] = $this->prepareHelp($pool->messages->getHelp('analyseProtected'));
        $help['analysePrivate'] = $this->prepareHelp($pool->messages->getHelp('analysePrivate'));
        $help['analyseTraversable'] = $this->prepareHelp($pool->messages->getHelp('analyseTraversable'));
        $help['debugMethods'] = __('Comma-separated list of used debug callback functions. kreXX will try to call them,' .
            "if they are available and display their provided data.\nWe Recommend for Magento: '__toArray,toString'");
        $help['level'] = $this->prepareHelp($pool->messages->getHelp('level'));
        $help['analyseProtectedMethods'] = $this->prepareHelp($pool->messages->getHelp('analyseProtectedMethods'));
        $help['analysePrivateMethods'] = $this->prepareHelp($pool->messages->getHelp('analysePrivateMethods'));
        $help['registerAutomatically'] = $this->prepareHelp($pool->messages->getHelp('registerAutomatically'));
        $help['analyseConstants'] = $this->prepareHelp($pool->messages->getHelp('analyseConstants'));
        $help['analyseGetter'] = $this->prepareHelp($pool->messages->getHelp('analyseGetter'));
        $help['useScopeAnalysis'] = $this->prepareHelp($pool->messages->getHelp('useScopeAnalysis'));
        $help['memoryLeft'] = $this->prepareHelp($pool->messages->getHelp('memoryLeft'));
        $help['maxRuntime'] = $this->prepareHelp($pool->messages->getHelp('maxRuntime'));
        $help['maxStepNumber'] = $this->prepareHelp($pool->messages->getHelp('maxStepNumber'));

        $this->assign('help', $help);

        // Initializing the select data for the template.
        $this->setSelectDestination(array(
            'browser' => __('browser'),
            'file' => __('file')
        ));
        $this->setSelectBool(array('true' => 'true', 'false' => 'false'));
        $this->setSelectBacktrace(array(
            'normal' => __('normal'),
            'deep' => __('deep')
        ));
        $skins = array();


        foreach ($pool->render->getSkinList() as $skin) {
            $skins[$skin] = $skin;
        }

        // Get all values from the configuration file.
        $settings['output']['skin'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'skin'
        );
        $settings['output']['maxfiles'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'maxfiles'
        );
        $settings['output']['destination'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'destination'
        );
        $settings['runtime']['maxCall'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'maxCall'
        );
        $settings['output']['disabled'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'disabled'
        );
        $settings['output']['iprange'] = $pool->config->iniConfig->getConfigFromFile(
            'output',
            'iprange'
        );
        $settings['runtime']['detectAjax'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'detectAjax'
        );
        $settings['properties']['analyseProtected'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseProtected'
        );
        $settings['properties']['analysePrivate'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analysePrivate'
        );
        $settings['properties']['analyseConstants'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseConstants'
        );
        $settings['properties']['analyseTraversable'] = $pool->config->iniConfig->getConfigFromFile(
            'properties',
            'analyseTraversable'
        );
        $settings['methods']['debugMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'debugMethods'
        );
        $settings['runtime']['level'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'level'
        );
        $settings['methods']['analyseProtectedMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analyseProtectedMethods'
        );
        $settings['methods']['analysePrivateMethods'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analysePrivateMethods'
        );
        $settings['backtraceAndError']['registerAutomatically'] = $pool->config->iniConfig->getConfigFromFile(
            'backtraceAndError',
            'registerAutomatically'
        );
        $settings['methods']['analyseGetter'] = $pool->config->iniConfig->getConfigFromFile(
            'methods',
            'analyseGetter'
        );
        $settings['runtime']['useScopeAnalysis'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'useScopeAnalysis'
        );
        $settings['runtime']['memoryLeft'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'memoryLeft'
        );
        $settings['runtime']['maxRuntime'] = $pool->config->iniConfig->getConfigFromFile(
            'runtime',
            'maxRuntime'
        );
        $settings['backtraceAndError']['maxStepNumber'] = $pool->config->iniConfig->getConfigFromFile(
            'backtraceAndError',
            'maxStepNumber'
        );

        // Are these actually set?
        foreach ($settings as $mainkey => $setting) {
            foreach ($setting as $attribute => $config) {
                if (is_null($config)) {
                    $factory[$attribute] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the factory settings!
                    $settings[$mainkey][$attribute] = $pool->config->configFallback[$mainkey][$attribute];
                } else {
                    $factory[$attribute] = '';
                }
            }
        }

        // Add them to the template.
        $this->assign('skins', $skins);
        $this->assign('settings', $settings);
        $this->assign('factory', $factory);
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild('save_config', 'Magento\Backend\Block\Widget\Button', [
                'label' => __('Save configuration'),
                'title' => __('Save configuration'),
                'onclick' => 'submitMainform();',
                'class' => 'action-default primary'
            ]);
        return parent::_prepareLayout();
    }

    /**
     * Prepares the help text from the kreXX library by:
     * - Removing all tags
     * - htmlspecialchar'ing
     * - using the translate underscore
     *
     * @param $string
     *   The string we need to prepare
     *
     * @return string
     *   The prepared string.
     */
    protected function prepareHelp($string)
    {
        return __(htmlspecialchars(strip_tags($string)));
    }
}
