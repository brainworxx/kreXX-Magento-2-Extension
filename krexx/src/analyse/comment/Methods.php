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

namespace Brainworxx\Krexx\Analyse\comment;

/**
 * We get the comment of a method and try to resolve the inheritdoc stuff.
 *
 * @package Brainworxx\Krexx\Analyse
 */
class Methods extends AbstractComment
{
    /**
     * Get the method comment and resolve the inheritdoc.
     *
     * Simple wrapper around the getMethodComment() to make sure
     * we only escape it once!
     *
     * @param \ReflectionMethod $reflectionMethod
     *   An already existing reflection of the method.
     * @param \ReflectionClass $reflectionClass
     *   An already existing reflection of the original class.
     *
     * @return string
     *   The prettified and escaped comment.
     */
    public function getComment(\ReflectionFunctionAbstract $reflectionMethod, \ReflectionClass $reflectionClass = null)
    {
        // Do some static caching. The comment will not change during a run.
        static $cache = array();
        $cachingKey = $reflectionClass->getName() . '::' . $reflectionMethod->getName();

        if (isset($cache[$cachingKey])) {
            return $cache[$cachingKey];
        }
        // Cache not found. We need to generate this one.
        $cache[$cachingKey] = $this->pool->encodeString(
            $this->getMethodComment($reflectionMethod, $reflectionClass)
        );
        return $cache[$cachingKey];
    }

    /**
     * Get the method comment and resolve the inheritdoc.
     *
     * @param \ReflectionMethod $reflectionMethod
     *   An already existing reflection of the method.
     * @param \ReflectionClass $reflectionClass
     *   An already existing reflection of the original class.
     *
     * @return string
     *   The prettified comment.
     */
    protected function getMethodComment(\ReflectionMethod $reflectionMethod, \ReflectionClass $reflectionClass = null)
    {
        // Get a first impression.
        $comment = $this->prettifyComment($reflectionMethod->getDocComment());

        if ($this->checkComment($comment)) {
            // Found it!
            return trim($comment);
        }

        // Check for interfaces.
        $comment = $this->getInterfaceComment($comment, $reflectionClass, $reflectionMethod->name);

        if ($this->checkComment($comment)) {
            // Found it!
            return trim($comment);
        }

        // Check for traits.
        $comment = $this->getTraitComment($comment, $reflectionClass, $reflectionMethod->name);

        if ($this->checkComment($comment)) {
            // Found it!
            return trim($comment);
        }

        // Nothing on this level, we need to take a look at the parent.
        try {
            $parentReflection = $reflectionClass->getParentClass();
            if (is_object($parentReflection)) {
                $parentMethod = $parentReflection->getMethod($reflectionMethod->name);
                if (is_object($parentMethod)) {
                    // Going deeper into the rabid hole!
                    $comment = trim($this->getMethodComment($parentMethod, $parentReflection));
                }
            }
        } catch (\ReflectionException $e) {
            // Too deep, comment not found :-(
        }

        // Still here? Tell the dev that we could not resolve the comment.
        $comment = $this->replaceInheritComment($comment, '::could not resolve the inherited comment comment::');
        return trim($comment);
    }

    /**
     * Gets the comment from all added traits.
     *
     * Iterated through an array of traits, to see
     * if we can resolve the inherited comment. Traits
     * are only supported since PHP 5.4, so we need to
     * check if they are available.
     *
     * @param string $originalComment
     *   The original comment, so far.
     * @param \ReflectionClass $reflection
     *   A reflection of the object we are currently analysing.
     * @param string $methodName
     *   The name of the method from which we ant to get the comment.
     *
     * @return string
     *   The comment from one of the trait.
     */
    protected function getTraitComment($originalComment, \ReflectionClass $reflection, $methodName)
    {
        // We need to check if we can get traits here.
        if (method_exists($reflection, 'getTraits')) {
            // Get the traits from this class.
            // Now we should have an array with reflections of all
            // traits in the class we are currently looking at.
            foreach ($reflection->getTraits() as $trait) {
                if ($this->checkComment($originalComment)) {
                    // Looks like we've resolved them all.
                    return $originalComment;
                }
                // We need to look further!
                if ($trait->hasMethod($methodName)) {
                    $traitComment = $this->prettifyComment(
                        $trait->getMethod($methodName)->getDocComment()
                    );
                    // Replace it.
                    $originalComment = $this->replaceInheritComment($originalComment, $traitComment);
                }
            }
            // Return what we could resolve so far.
            return $originalComment;
        }

        // Wrong PHP version. Traits are not available.
        return $originalComment;
    }

    /**
     * Gets the comment from all implemented interfaces.
     *
     * Iterated through an array of interfaces, to see
     * if we can resolve the inherited comment.
     *
     * @param string $originalComment
     *   The original comment, so far.
     * @param \ReflectionClass $reflectionClass
     *   A reflection of the object we are currently analysing.
     * @param string $methodName
     *   The name of the method from which we ant to get the comment.
     *
     * @return string
     *   The comment from one of the interfaces.
     */
    protected function getInterfaceComment($originalComment, \ReflectionClass $reflectionClass, $methodName)
    {
        foreach ($reflectionClass->getInterfaces() as $interface) {
            if ($this->checkComment($originalComment)) {
                // Looks like we've resolved them all.
                return $originalComment;
            }
            // We need to look further.
            if ($interface->hasMethod($methodName)) {
                $interfaceComment = $this->prettifyComment($interface->getMethod($methodName)->getDocComment());
                // Replace it.
                $originalComment = $this->replaceInheritComment($originalComment, $interfaceComment);
            }
        }
        // Return what we could resolve so far.
        return $originalComment;
    }
}
