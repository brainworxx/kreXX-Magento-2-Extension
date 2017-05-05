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

namespace Brainworxx\M2krexx\Controller\Adminhtml\Logging;

use Magento\Framework\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem\File\ReadFactory;

class View extends Action
{
    /**
     * The redirect answer.
     *
     * @var Redirect
     */
    protected $resultRedirect;

    /**
     * The raw result (aka the logfile content).
     *
     * @var Raw
     */
    protected $resultRaw;

    /**
     * Basic file operations.
     *
     * @var File
     */
    protected $ioFile;

    /**
     * Filesystem
     *
     * @var ReadFactory
     */
    protected $fileReadFactory;

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

        $this->resultRaw = $objectManager->get(Raw::class);
        $this->ioFile = $objectManager->get(File::class);
        $this->urlBuilder = $objectManager->get(UrlInterface::class);
        $this->resultRedirect = $objectManager->get(Redirect::class);
        $this->fileReadFactory = $objectManager->get(ReadFactory::class);

        parent::__construct($context);

    }

    /**
     * Delete the files with the id, set the success message and return to the
     * log overview.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Sanitize the id.
        $id = preg_replace('/[^0-9]/', '', $this->getRequest()->getParam('id'));
        // Get the filepath.
        $file = \Krexx::$pool->config->getLogDir() . $id . '.Krexx.html';

        if ($this->ioFile->fileExists($file, true)) {
            // Open the file and dispatch it!
            /** @var \Magento\Framework\Filesystem\File\Read $read */
            $read = $this->fileReadFactory->create($file, DriverPool::FILE);

            while (!$read->eof()) {
                // Use output buffering.
                ob_flush();
                flush();
                // Get new data.
                $stream = $read->read(1024);
                echo $stream;
            }
            $read->close();

            // Do nothing. Tell the framework that we are done.
            return $this->resultRaw;
        } else {
            // No file to send.   :-(
            // Tell the user that the file was not readable.
            $this->resultRedirect->setUrl($this->urlBuilder->getUrl('m2krexx/logging/index'));
            $this->messageManager->addError('Unable to open logfile id: ' . $id . '!');
            // Redirect to the log index.
            return $this->resultRedirect;
        }



    }
}