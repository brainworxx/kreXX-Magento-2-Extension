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
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{

    /**
     * Building Uris fro fun and profit.
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * File access.
     *
     * @var File
     */
    protected $fileIo;

    /**
     * Escaping the oputput for security reasons.
     *
     * @var Escaper
     */
    protected $escaper;

    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        // Getting out act together.
        $objectManager = ObjectManager::getInstance();
        $this->urlBuilder = $objectManager->get(UrlInterface::class);
        $this->fileIo = $objectManager->get(File::class);
        $this->escaper = $objectManager->create(Escaper::class);

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
        $timestamp = filemtime($row['filename']);
        $fileSize = $this->fileSizeConvert(filesize($row['filename']));
        $id = (int)str_replace('.Krexx.html', '', $row['basename']);

        $result = array(
            'id' => $id,
            'filename' => $this->generateLinkToViewAction($row['basename'], $id),
            'timestamp' => $timestamp,
            'date' => date("d.m.y H:i:s", $timestamp),
            'size' => $fileSize,
            'meta_analysis_of' => '',
            'meta_called_in' => '',
        );

        // Parsing a potentially 80MB file for it's content is not a good idea.
        // That is why the kreXX lib provides a meta data file. We will open
        // this file and add it's content to the template.
        if (is_readable($row['filename'] . '.json')) {
            $fileinfo['meta'] = json_decode($this->fileIo->read($row['filename'] . '.json'), true);
            foreach ($fileinfo['meta'] as &$meta) {
                $result['meta_called_in'] .= $this->escaper->escapeHtml(basename($meta['file'])) .
                    ' in line <b>' .  $this->escaper->escapeHtml($meta['line']) . '</b><hr/>';

                $result['meta_analysis_of'] .= $this->escaper->escapeHtml($meta['type']) .
                    ': <b>' . $this->escaper->escapeHtml($meta['varname']) . '</b><hr/>';
            }

            $result['meta_called_in'] = substr($result['meta_called_in'], 0, -3);
            $result['meta_analysis_of'] = substr($result['meta_analysis_of'], 0, -3);
        }


        return $result;
    }

    /**
     * Generate a link to the logfile dispatcher for thsi file.
     *
     * @param string $basename
     * @param int $id
     *
     * @return string
     *   The generated link to the dispatcher.
     */
    protected function generateLinkToViewAction($basename, $id)
    {
        $url = $this->urlBuilder->getUrl('m2krexx/logging/view', ['id' => $id]);
        return '<a target="_blank" href="' . $url . '">' . $this->escaper->escapeHtml($basename) . '</a>';
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
