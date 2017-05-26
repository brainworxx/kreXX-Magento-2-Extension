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

namespace Brainworxx\M2krexx\Controller\Adminhtml\Config;

use Magento\Framework\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;

class SaveEdit extends Action
{
    /**
     * List of all setting-nanes for which we are accepting values.
     *
     * @var array
     */
    protected $allowedSettingsNames = array(
        'skin',
        'maxfiles',
        'destination',
        'maxCall',
        'disabled',
        'detectAjax',
        'analyseProtected',
        'analysePrivate',
        'analyseTraversable',
        'debugMethods',
        'level',
        'analyseProtectedMethods',
        'analysePrivateMethods',
        'registerAutomatically',
        'backtraceAnalysis',
        'analyseConstants',
        'iprange',
        'memoryLeft',
        'maxRuntime',
        'useScopeAnalysis',
        'analyseGetter',
        'maxStepNumber',
    );

    /**
     * List of all sections for which we are accepting values
     *
     * @var array
     */
    protected $allowedSections = array(
        'runtime',
        'output',
        'properties',
        'methods',
        'backtraceAndError',
    );

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Brainworxx_M2krexx::configure';

    /**
     * The redirect back to the logfile overview.
     *
     * @var Redirect
     */
    protected $resultRedirect;

    /**
     * @var File
     */
    protected $ioFile;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(Context $context)
    {
        $objectManager = ObjectManager::getInstance();

        $this->resultRedirect = $objectManager->get(Redirect::class);
        $this->ioFile = $objectManager->get(File::class);
        $this->urlBuilder = $objectManager->get(UrlInterface::class);

        parent::__construct($context);

    }

    /**
     * Save the data from the form to the kreXX config file.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $arguments = $this->getRequest()->getParams();
        $all_ok = true;
        $pool = \Krexx::$pool;

        $filepath = $pool->krexxDir . 'config/Krexx.ini';
        // We must preserve the section 'feEditing'.
        // Everything else will be overwritten.
        if ($this->ioFile->fileExists($filepath)) {
            $old_values = parse_ini_file($filepath, true);
        } else {
            $old_values = array();
        }
        if (isset($old_values['feEditing'])) {
            $old_values = array('feEditing' => $old_values['feEditing']);
        } else {
            $old_values = array('feEditing' => array());
        }

        // Iterating through the form.
        foreach ($arguments as $section => $data) {
            if (is_array($data) && in_array($section, $this->allowedSections)) {
                // We've got a section key.
                foreach ($data as $setting_name => $value) {
                    if (in_array($setting_name, $this->allowedSettingsNames)) {
                        // We escape the value, just in case, since we can not whitelist it.
                        $value = htmlspecialchars(preg_replace('/\s+/', '', $value));
                        // Evaluate the setting!
                        if ($pool->config->security->evaluateSetting($section, $setting_name, $value)) {
                            $old_values[$section][$setting_name] = $value;
                        } else {
                            // Validation failed! kreXX will generate a message, which we will
                            // display at the buttom.
                            $all_ok = false;
                        }
                    }
                }
            }
        }

        // Now we must create the ini file.
        $ini = '';
        foreach ($old_values as $key => $setting) {
            $ini .= '[' . $key . ']' . PHP_EOL;
            foreach ($setting as $setting_name => $value) {
                $ini .= $setting_name . ' = "' . $value . '"' . PHP_EOL;
            }
        }

        // Now we should write the file!
        $fileService  = $pool->createClass('Brainworxx\\Krexx\\Service\\Misc\\File');
        if ($all_ok) {
            if ($this->ioFile->write($filepath, $ini) === false) {
                $all_ok = false;
                $pool->messages->addMessage('Configuration file ' .
                    $fileService->filterFilePath($filepath) .
                    ' is not writeable!');
            }
        }

        // Something went wrong, we need to tell the user.
        if (!$all_ok) {
            $this->messageManager->addError(
                strip_tags(__($pool->messages->outputMessages())) . '<br />' . __('The data was NOT saved.')
            );
        } else {
            $this->messageManager->addSuccess(
                __('The settings were saved to:') . '<br />' .
                $fileService->filterFilePath($filepath)
            );
        }

        // Set the redirect url.
        $this->resultRedirect->setUrl($this->urlBuilder->getUrl('m2krexx/config/edit'));

        // Return the redirect.
        return $this->resultRedirect;

    }
}