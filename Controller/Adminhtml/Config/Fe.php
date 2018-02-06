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
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Brainworxx\Krexx\Service\Factory\Pool;

class Fe extends Action
{
    protected $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Brainworxx_M2krexx::configure';

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(Context $context)
    {
        $this->resultPageFactory = ObjectManager::getInstance()->get(PageFactory::class);
        parent::__construct($context);

        // Has kreXX something to say? Maybe a writeprotected logfolder?
        // We are only facing error messages here, normally.
        Pool::createPool();
        $messages = strip_tags(\Krexx::$pool->messages->outputMessages());
        if (!empty($messages)) {
            $this->messageManager->addError($messages);
        }
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pageResult = $this->resultPageFactory->create();
        $pageResult->getConfig()->getTitle()->set(__('kreXX Debugger'));
        $pageResult->getConfig()->getTitle()->prepend(__('Edit kreXX FE Configuration'));

        return $pageResult;
    }
}