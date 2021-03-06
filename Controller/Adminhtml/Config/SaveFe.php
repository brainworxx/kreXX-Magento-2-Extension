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

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Brainworxx\Krexx\Service\Factory\Pool;

class SaveFe extends Action
{
    /**
     * List of all setting-nanes for which we are accepting values.
     *
     * @var array
     */
    protected $allowedSettingsNames = array(
        'skin',
        'maxCall',
        'disabled',
        'detectAjax',
        'analyseProtected',
        'analysePrivate',
        'analyseTraversable',
        'level',
        'analyseProtectedMethods',
        'analysePrivateMethods',
        'registerAutomatically',
        'backtraceAnalysis',
        'analyseConstants',
        'memoryLeft',
        'maxRuntime',
        'useScopeAnalysis',
        'analyseGetter',
        'maxStepNumber',
        'arrayCountLimit',
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
        'pruneOutput',
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
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(Context $context)
    {
        $objectManager = ObjectManager::getInstance();

        $this->resultRedirect = $objectManager->get(Redirect::class);
        $this->ioFile = $objectManager->get(File::class);
        $this->urlBuilder = $objectManager->get(UrlInterface::class);

        parent::__construct($context);
        Pool::createPool();
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
        $filepath = KREXX_DIR . 'config/Krexx.ini';

        // Whitelist of the vales we are accepting.
        $allowed_values = array('full', 'display', 'none');

        // Get the old values . . .
        if ($this->ioFile->fileExists($filepath)) {
            $old_values = parse_ini_file($filepath, true);
            // . . . and remove our part.
            unset($old_values['feEditing']);
        } else {
            $old_values = array();
        }

        // Iterating through the form.
        foreach ($arguments as $key => $data) {
            if (is_array($data)) {
                foreach ($data as $setting_name => $value) {
                    if (in_array($value, $allowed_values) && in_array($setting_name, $this->allowedSettingsNames)) {
                        // Whitelisted values are ok.
                        $old_values['feEditing'][$setting_name] = $value;
                    } else {
                        // Validation failed!
                        $all_ok = false;
                        $pool->messages->addMessage(htmlentities($value) . ' is not an allowed value!');
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
        if ($all_ok) {
            if ($this->ioFile->write($filepath, $ini) === false) {
                $all_ok = false;
                $pool->messages->addMessage(
                    'Configuration file %s is not writable!',
                    array($pool->fileService->filterFilePath($filepath))
                );
            }
        }

        // Something went wrong, we need to tell the user.
        if ($all_ok) {
            $this->messageManager->addSuccess(
                __('The settings were saved to:') . '<br />' .
                $pool->fileService->filterFilePath($filepath)
            );
        } else {
            $this->messageManager->addError(
                strip_tags(__($pool->messages->outputMessages())) . '<br />' . __('The data was NOT saved.')
            );
        }

        // Set the redirect url.
        $this->resultRedirect->setUrl($this->urlBuilder->getUrl('m2krexx/config/fe'));

        // Return the redirect.
        return $this->resultRedirect;

    }
}