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

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Brainworxx_M2krexx',
    __DIR__
);

// Register the overwrites for Magento
if (!isset($GLOBALS['kreXXoverwrites'])) {
    $GLOBALS['kreXXoverwrites'] = array();
}
$GLOBALS['kreXXoverwrites']['classes'] = array(
    // Special getter analysis for the dynamic getters of the data object.
    'Brainworxx\\Krexx\\Analyse\\Callback\\Iterate\\ThroughGetter' => 'Brainworxx\\M2krexx\\Overwrites\\Dynamicgetter',
    // Fewer debug methods to prevent an output overkill.
    'Brainworxx\\Krexx\\Service\\Config\\Config' => 'Brainworxx\\M2krexx\\Overwrites\\Config',
);

// This should trigger the autoloader.
// If not, we will use the bundeled version
// @todo Do we really need to bundle kreXX ?!?
if (!class_exists('Krexx')) {
    include 'krexx/Krexx.php';
}
