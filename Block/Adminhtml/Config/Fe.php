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
use Brainworxx\Krexx\Service\Factory\Pool;

/**
 * Add the form data for the cofig file editor to the template.
 *
 * @package Brainworxx\M2krexx\Block\Adminhtml\Config
 */
class Fe extends Template
{

    /**
     * {@inheritdoc}
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        // Generate the values for the select elements.
        $data = array();
        $settings = array();
        $factory = array();
        Pool::createPool();
        $pool = \Krexx::$pool;

        // Setting possible form values.
        $data['settings'] = array(
            'full' => 'full edit',
            'display' => 'display only',
            'none' => 'do not display',
        );

        // See, if we have any values in the configuration file.
        // See, if we have any values in the configuration file.
        $settings['output']['skin'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('skin')
        );
        $settings['runtime']['maxCall'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('maxCall')
        );
        $settings['output']['disabled'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('disabled')
        );
        $settings['runtime']['detectAjax'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('detectAjax')
        );
        $settings['properties']['analyseProtected'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analyseProtected')
        );
        $settings['properties']['analysePrivate'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analysePrivate')
        );
        $settings['properties']['analyseTraversable'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analyseTraversable')
        );
        $settings['properties']['analyseConstants'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analyseConstants')
        );
        $settings['runtime']['level'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('level')
        );
        $settings['methods']['analyseProtectedMethods'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analyseProtectedMethods')
        );
        $settings['methods']['analysePrivateMethods'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analysePrivateMethods')
        );
        $settings['pruneOutput']['maxStepNumber'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('maxStepNumber')
        );
        $settings['pruneOutput']['arrayCountLimit'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('arrayCountLimit')
        );
        $settings['runtime']['memoryLeft'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('memoryLeft')
        );
        $settings['runtime']['maxRuntime'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('maxRuntime')
        );
        $settings['methods']['analyseGetter'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('analyseGetter')
        );
        $settings['runtime']['useScopeAnalysis'] = $this->convertKrexxFeSetting(
            $pool->config->iniConfig->getFeConfigFromFile('useScopeAnalysis')
        );

        // Are these actually set?
        foreach ($settings as $mainkey => $setting) {
            foreach ($setting as $attribute => $config) {
                if (empty($config)) {
                    $factory[$attribute] = ' checked="checked" ';
                    // We need to fill these values with the stuff from the
                    // factory settings!
                    $settings[$mainkey][$attribute] = $this->convertKrexxFeSetting(
                        $pool->config->feConfigFallback[$attribute]
                    );
                } else {
                    $factory[$attribute] = '';
                }
            }
        }

        $this->assign('data', $data);
        $this->assign('settings', $settings);
        $this->assign('factory', $factory);
    }

    /**
     * Converts the kreXX FE config setting.
     *
     * Letting people choose what kind of form element will
     * be used does not really make sense. We will convert the
     * original kreXX settings to a more useable form for the editor.
     *
     * @param array $values
     *   The values we want to convert.
     *
     * @return string
     *   The converted value
     */
    protected function convertKrexxFeSetting($values)
    {
        $result = '';
        if (is_array($values)) {
            // The values are:
            // full    -> is editable and values will be accepted
            // display -> we will only display the settings
            // The original values include the name of a template partial
            // with the form element.
            if ($values['type'] == 'None') {
                // It's not visible, thus we do not accept any values from it.
                $result = 'none';
            }
            if ($values['editable'] == 'true' && $values['type'] != 'None') {
                // It's editable and visible.
                $result = 'full';
            }
            if ($values['editable'] == 'false' && $values['type'] != 'None') {
                // It's only visible.
                $result = 'display';
            }
        }
        return $result;
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild('save_fe', 'Magento\Backend\Block\Widget\Button', [
                'label' => __('Save configuration'),
                'title' => __('Save configuration'),
                'onclick' => 'submitMainform();',
                'class' => 'action-default primary'
            ]);
        return parent::_prepareLayout();
    }
}
