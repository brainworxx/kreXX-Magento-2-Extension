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

namespace Brainworxx\M2krexx\Overwrites;

use Brainworxx\Krexx\Analyse\Code\Codegen;
use Brainworxx\Krexx\Analyse\Callback\Iterate\ThroughGetter;

/**
 * Analyses all the dynamic getter methods of a varien object.
 *
 * @uses array methodList
 *   The list of all methods we are analysing
 * @uses \ReflectionClass $ref
 *   A reflection class of the object we are analysing.
 */
class Dynamicgetter extends ThroughGetter
{
    /**
     * {@inheritdoc}
     */
    public function callMe()
    {
        $output = '';

        // Try to get the _data protected property from the class.
        $dataArray = $this->getTheUnderscoreDataArray($this->parameters['ref']);

        foreach ($dataArray as $key => $value) {
            // Transform the $key to getCamelCase.
            $callback = function ($matches) {
                foreach ($matches as $match) {
                    return strtoupper($match);
                }
                return '';
            };

            $key = 'get' . preg_replace_callback('/(?:^|_)(.?)/', $callback, $key);

            // Prepare the model.
            /** @var \Brainworxx\Krexx\Analyse\Model $model */
            $model = $this->pool->createClass('Brainworxx\\Krexx\\Analyse\\Model')
                ->setName($key)
                ->setConnectorType(Codegen::METHOD)
                ->setData($value)
                ->addToJson('hint', 'Magento\Framework\DataObject magic getter method.');

            // Check if there is a getter method in there. We should add the
            // comment to the model.
            foreach ($this->parameters['methodList'] as $id => $reflectionMethod) {
                /** @var \ReflectionMethod $reflectionMethod */
                $name = $reflectionMethod->getName();
                if ($name === $key) {
                    $model->addToJson('method comment', nl2br($this->pool
                    ->createClass('Brainworxx\\Krexx\\Analyse\\Comment\\Methods')
                    ->getComment(
                        $reflectionMethod,
                        $this->parameters['ref']
                    )));
                    // Remove the reflection from the list. We still need to
                    // process the parent, and don't want to have double entries in
                    // there.
                    unset($this->parameters['methodList'][$id]);
                    break;
                }
            }

            // And send the result on it's way.
            $output .= $this->pool
                    ->routing
                    ->analysisHub($model);
        }

        return $output . parent::callMe();
    }

    /**
     * Extracts the _data value from the object.
     *
     * @param \reflectionClass $ref
     *
     * @return array
     *   The extracted values.
     */
    protected function getTheUnderscoreDataArray($ref)
    {
        $data = array();

        if (is_a($this->parameters['data'], '\\Magento\\Framework\\DataObject')) {
            if ($ref->hasProperty('_data')) {
                $refProp = $ref->getProperty('_data');
                $refProp->setAccessible(true);
                $data = $refProp->getValue($this->parameters['data']);
                if (!is_array($data)) {
                    // No Properties, it's empty.
                    $data = array();
                }
            }
        }

        return $data;
    }
}
