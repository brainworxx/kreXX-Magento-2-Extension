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

namespace Brainworxx\M2krexx\Model\Logging;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Brainworxx\M2krexx\Helper\Data;
use Magento\Framework\Data\Collection\Filesystem;
use Magento\Framework\App\ObjectManager;

class Collection extends Filesystem
{
    /**
     * Filenames regex filter
     * @todo wtf ist this?!?
     *
     * @var string
     */
    protected $allowedFilesMask = '/^[a-z0-9\.\-\_]+[bar]\.log/i';

    /**
     * @var \Brainworxx\M2krexx\Helper\Data $helper
     */
    protected $helper;

    public function __construct(EntityFactoryInterface $entityFactory)
    {
        $this->helper = ObjectManager::getInstance()->get(Data::class);
        parent::__construct($entityFactory);

        /** @var string $logDirectory */
        $logDirectory = \Krexx::$pool->config->getLogDir();

        $this
            // We only want to parse the log dir, and nothing else.
            ->setCollectRecursively(false)
            // Set the logdir, which is configured in kreXX
            ->addTargetDir($logDirectory)
            // Set filter to all files ending with .Krexx.html
            ->setFilesFilter('/.*\.Krexx\.html$/')
            // Sort the files by timestamp.
            ->setOrder('timestamp');
    }

    /**
     * @param string $filename
     *
     * @return array
     */
    protected function _generateRow($filename)
    {
        /** @var array $row */
        $row = parent::_generateRow($filename);
        $row = $this->helper->generateRow($row);

        return $row;
    }

    /**
     * @param $field
     * @param $direction
     * @return \Magento\Framework\Data\Collection
     */
    public function addOrder($field, $direction)
    {
        return $this->setOrder($field, $direction);
    }
}
