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

namespace Brainworxx\M2krexx\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Context $context
     */
    public function __construct(Context $context, \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * Generates the rows for the admin grid, used to access the logfiles.
     *
     * @param array $row
     *
     * @return array
     */
    public function generateRow(array $row)
    {
        // @todo: Add the following to the data
        // [id] from the filename (14926070945794300)
        // [filename] alias basename
        // [date] from the file (last modified)
        // [size] from the file
        // [meta_analysis_of] 'Analysis of ...'
        // [meta_called_in] 'Called in' (file and line)

        $result = array(
            'id' => (int)str_replace('.Krexx.html', '', $row['basename']),
            'filename' => $row['basename'],
            'date' => date("d.m.y H:i:s", filemtime($row['filename'])),
            'size' => $this->fileSizeConvert(filesize($row['filename'])),
            'meta_analysis_of' => 'todo',
            'meta_called_in' => 'todo',
        );

        // Parsing a potentialls 80MB file for it's content is not a good idea.
        // That is why the kreXX lib provides some meta data. We will open
        // this file and add it's content to the template.
        if (is_readable($row['filename'] . '.json')) {
            /** @var \Magento\Framework\Filesystem\Io\File $ioFile */
            $ioFile = $this->objectManager->get(\Magento\Framework\Filesystem\Io\File::class);
            $fileinfo['meta'] = json_decode($ioFile->read($row['filename'] . '.json'), true);

            foreach ($fileinfo['meta'] as &$meta) {
                $meta['filename'] = basename($meta['file']);
            }
        }
//        krexx($fileinfo);

        return $result;
    }

    /**
     * Converts bytes into human readable file size.
     *
     * @author Mogilev Arseny
     *
     * @param string $bytes
     *   The bytes value we want to make readable.
     *
     * @return string
     *   Human readable file size.
     */
    protected function fileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4),
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3),
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2),
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024,
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1,
            ),
        );

        $result = '';
        foreach ($arBytes as $aritem) {
            if ($bytes >= $aritem["VALUE"]) {
                $result = $bytes / $aritem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $aritem["UNIT"];
                break;
            }
        }
        return $result;
    }
}
