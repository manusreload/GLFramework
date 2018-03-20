<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace GLFramework\Tests;

use Exception;
use GLFramework\Bootstrap;
use GLFramework\DatabaseManager;
use GLFramework\DBStructure;
use GLFramework\Response;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 9:59
 */

/**
 * Class TestCase
 * @package GLFramework\Tests
 */
class TestCase extends PHPUnit\Framework\TestCase
{
    /**
     * @var Crawler
     */
    protected $crawler;
    /**
     * @var Response
     */
    protected $response;
    protected $inputs = array();
    protected $uploads = array();
    private $internal_host = 'http://localhost';
    private $redirections = array();

    private $models_delete = array();

    private $database;

    /**
     * TODO
     */
    public function requireDatabase()
    {
        if ($this->database === null) {
            $this->database = new DatabaseManager();
            $this->assertTrue($this->database->connect(), 'Can not connect to database for testing!');
            $db2 = new DBStructure();
            $res = $db2->executeModelChanges($this->database);
            $this->assertNotInstanceOf(\Exception::class, $res, 'Error installing models');
        }
    }

    /**
     * TODO
     *
     * @param $model
     */
    public function removeLater($model)
    {
        if (!is_array($model)) {
            $model = array($model);
        }
        foreach ($model as $m) {
            $this->models_delete[] = $m;
        }
    }

    /**
     * TODO
     *
     * @return DatabaseManager
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * TODO
     *
     * @param $url
     * @return TestCase
     */
    public function visit($url)
    {
        return $this->call('GET', $url);
    }

    /**
     * TODO
     *
     * @param $form Form
     * @param array $uploads
     * @return $this|TestCase
     */
    public function makeRequestUsingForm($form, $uploads = array())
    {
        $method = $form->getMethod();
        $uri = $form->getUri();
        parse_str(http_build_query($form->getValues()), $parameters);
        return $this->call($method, $uri, $parameters, [], $uploads);
    }

    /**
     * TODO
     *
     * @param $method
     * @param $uri
     * @param array $params
     * @param array $cookies
     * @param array $files
     * @return $this
     */
    public function call($method, $uri, $params = array(), $cookies = array(), $files = array())
    {
        $uri = str_replace($this->internal_host, '', $uri);
        if (strpos($uri, '/') !== 0) {
            $uri = '/' . $uri;
        }
        if(($i = strpos($uri, "#")) > 0) {
            $uri = substr($uri,0 , $i);
        }
        $bs = Bootstrap::getSingleton();
        $_COOKIE = $cookies;
        $_REQUEST = $params;
        $_POST = $params;
        $_FILES = $this->buildFilesForm($files);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_HOST'] = substr($this->internal_host, 7);
        $this->response = $bs->run($uri, $method);
        $this->followRedirects();
        $this->crawler = new Crawler($this->response->getContent(), $this->internal_host . $uri);

        return $this;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @param array $cookies
     * @param array $files
     */
    public function ajax($method, $uri, $params = array(), $cookies = array(), $files = array())
    {

    }


    /**
     * TODO
     *
     * @return $this
     */
    public function followRedirects()
    {
        if ($this->response->isRedirect()) {
            $key = $this->response->getRedirection();
            if (!isset($this->redirections[$key])) {
                $this->redirections[$key] = 0;
            }
            $this->redirections[$key] += 1;
            $this->assertLessThan(10, $this->redirections[$this->response->getRedirection()],
                "Cyclic redirection: " . $this->response->getRedirection());
            $this->call('GET', $this->response->getRedirection());
        }
        return $this;
    }

    /**
     * TODO
     *
     * @param $files
     * @return array
     */
    public function buildFilesForm($files)
    {
        $result = array();
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_string($file)) {
                    if (file_exists($file)) {
                        $this->buildFile(0, $result, $file);
                    } elseif ($file === '') {
                        $this->buildFile(4, $result);
                    }
                }
            }
        } else {
            if (file_exists($files)) {
                $this->buildFile(0, $result, $files);
            } else if ($files === '') {
                $this->buildFile(4, $result);
            }
        }
        return $result;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->response->getContent();
    }

    /**
     * TODO
     *
     * @param $text
     * @param bool $negate
     * @return $this
     */
    public function see($text, $negate = false)
    {
        $method = $negate ? 'assertNotRegExp' : 'assertRegExp';
        $rawPattern = preg_quote($text, '/');
        $escapedPattern = preg_quote(e($text), '/');
        $pattern = $rawPattern == $escapedPattern ? $rawPattern : "({$rawPattern}|{$escapedPattern})";
        $this->$method("/$pattern/i", $this->getContent());
        return $this;
    }

    /**
     * Assert that a given link is seen on the page.
     *
     * @param  string $text
     * @param  string|null $url
     * @return $this
     */
    public function seeLink($text, $url = null)
    {
        $message = "No links were found with expected text [{$text}]";
        if ($url) {
            $message .= " and URL [{$url}]";
        }
        $this->assertTrue($this->hasLink($text, $url), "{$message}.");
        return $this;
    }

    /**
     * Assert that a given link is not seen on the page.
     *
     * @param  string $text
     * @param  string|null $url
     * @return $this
     */
    public function dontSeeLink($text, $url = null)
    {
        $message = "A link was found with expected text [{$text}]";
        if ($url) {
            $message .= " and URL [{$url}]";
        }
        $this->assertFalse($this->hasLink($text, $url), "{$message}.");
        return $this;
    }

    /**
     * Assert that an input field contains the given value.
     *
     * @param  string $selector
     * @param  string $expected
     * @return $this
     */
    public function seeInField($selector, $expected)
    {
        $this->assertSame($expected, $this->getInputOrTextAreaValue($selector),
            "The field [{$selector}] does not contain the expected value [{$expected}].");
        return $this;
    }

    /**
     * Assert that an input field does not contain the given value.
     *
     * @param  string $selector
     * @param  string $value
     * @return $this
     */
    public function dontSeeInField($selector, $value)
    {
        $this->assertNotSame($this->getInputOrTextAreaValue($selector), $value,
            "The input [{$selector}] should not contain the value [{$value}].");
        return $this;
    }

    /**
     * Assert that the given checkbox is selected.
     *
     * @param  string $selector
     * @return $this
     */
    public function seeIsChecked($selector)
    {
        $this->assertTrue($this->isChecked($selector), "The checkbox [{$selector}] is not checked.");
        return $this;
    }

    /**
     * Assert that the given checkbox is not selected.
     *
     * @param  string $selector
     * @return $this
     */
    public function dontSeeIsChecked($selector)
    {
        $this->assertFalse($this->isChecked($selector), "The checkbox [{$selector}] is checked.");
        return $this;
    }

    /**
     * Assert that the expected value is selected.
     *
     * @param  string $selector
     * @param  string $expected
     * @return $this
     */
    public function seeIsSelected($selector, $expected)
    {
        $this->assertEquals($expected, $this->getSelectedValue($selector),
            "The field [{$selector}] does not contain the selected value [{$expected}].");
        return $this;
    }

    /**
     * Assert that the given value is not selected.
     *
     * @param  string $selector
     * @param  string $value
     * @return $this
     */
    public function dontSeeIsSelected($selector, $value)
    {
        $this->assertNotEquals($value, $this->getSelectedValue($selector),
            "The field [{$selector}] contains the selected value [{$value}].");
        return $this;
    }

    /**
     * TODO
     */
    protected function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        foreach ($this->models_delete as $model) {
            $model->delete();
        }
    }

    /**
     * TODO
     *
     * @param $text
     * @return TestCase
     */
    protected function dontSee($text)
    {
        return $this->see($text, true);
    }

    /**
     * TODO
     *
     * @param $element
     * @param $text
     * @param bool $negate
     * @return $this
     */
    protected function seeInElement($element, $text, $negate = false)
    {
        $method = $negate ? 'assertNotRegExp' : 'assertRegExp';
        $rawPattern = preg_quote($text, '/');
        $escapedPattern = preg_quote(e($text), '/');
        $content = $this->crawler->filter($element)->html();
        $pattern = $rawPattern == $escapedPattern ? $rawPattern : "({$rawPattern}|{$escapedPattern})";
        $this->$method("/$pattern/i", $content);
        return $this;
    }

    /**
     * Get the value of an input or textarea.
     *
     * @param  string $selector
     * @return string
     *
     * @throws \Exception
     */
    protected function getInputOrTextAreaValue($selector)
    {
        $field = $this->filterByNameOrId($selector, ['input', 'textarea']);
        if ($field->count() === 0) {
            throw new Exception("There are no elements with the name or ID [$selector].");
        }
        $element = $field->nodeName();
        if ($element === 'input') {
            return $field->attr('value');
        }
        if ($element === 'textarea') {
            return $field->text();
        }
        throw new Exception("Given selector [$selector] is not an input or textarea.");
    }

    /**
     * Get the selected value of a select field or radio group.
     *
     * @param  string $selector
     * @return string|null
     *
     * @throws \Exception
     */
    protected function getSelectedValue($selector)
    {
        $field = $this->filterByNameOrId($selector);
        if ($field->count() === 0) {
            throw new Exception("There are no elements with the name or ID [$selector].");
        }
        $element = $field->nodeName();
        if ($element === 'select') {
            return $this->getSelectedValueFromSelect($field);
        }
        if ($element === 'input') {
            return $this->getCheckedValueFromRadioGroup($field);
        }
        throw new Exception("Given selector [$selector] is not a select or radio group.");
    }

    /**
     * Get the selected value from a select field.
     *
     * @param  \Symfony\Component\DomCrawler\Crawler $field
     * @return string|null
     *
     * @throws \Exception
     */
    protected function getSelectedValueFromSelect(Crawler $field)
    {
        if ($field->nodeName() !== 'select') {
            throw new Exception('Given element is not a select element.');
        }
        foreach ($field->children() as $option) {
            if ($option->hasAttribute('selected')) {
                return $option->getAttribute('value');
            }
        }
        return;
    }

    /**
     * Get the checked value from a radio group.
     *
     * @param  \Symfony\Component\DomCrawler\Crawler $radioGroup
     * @return string|null
     *
     * @throws \Exception
     */
    protected function getCheckedValueFromRadioGroup(Crawler $radioGroup)
    {
        if ($radioGroup->nodeName() !== 'input' || $radioGroup->attr('type') !== 'radio') {
            throw new Exception('Given element is not a radio button.');
        }
        foreach ($radioGroup as $radio) {
            if ($radio->hasAttribute('checked')) {
                return $radio->getAttribute('value');
            }
        }
        return;
    }

    /**
     * Return true if the given checkbox is checked, false otherwise.
     *
     * @param  string $selector
     * @return bool
     *
     * @throws \Exception
     */
    protected function isChecked($selector)
    {
        $checkbox = $this->filterByNameOrId($selector, "input[type='checkbox']");
        if ($checkbox->count() == 0) {
            throw new Exception("There are no checkbox elements with the name or ID [$selector].");
        }
        return $checkbox->attr('checked') !== null;
    }

    /**
     * Click a link with the given body, name, or ID attribute.
     *
     * @param  string $name
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    protected function click($name)
    {
        $link = $this->crawler->selectLink($name);
        if (!count($link)) {
            $link = $this->filterByNameOrId($name, 'a');
            if (!count($link)) {
                throw new InvalidArgumentException("Could not find a link with a body, name, or ID attribute of [{$name}].");
            }
        }
        $this->visit($link->link()->getUri());
        return $this;
    }

    /**
     * Fill an input field with the given text.
     *
     * @param  string $text
     * @param  string $element
     * @return $this
     */
    protected function type($text, $element)
    {
        return $this->storeInput($element, $text);
    }

    /**
     * Check a checkbox on the page.
     *
     * @param  string $element
     * @return $this
     */
    protected function check($element)
    {
        return $this->storeInput($element, true);
    }

    /**
     * Uncheck a checkbox on the page.
     *
     * @param  string $element
     * @return $this
     */
    protected function uncheck($element)
    {
        return $this->storeInput($element, false);
    }

    /**
     * Select an option from a drop-down.
     *
     * @param  string $option
     * @param  string $element
     * @return $this
     */
    protected function select($option, $element)
    {
        return $this->storeInput($element, $option);
    }

    /**
     * Attach a file to a form field on the page.
     *
     * @param  string $absolutePath
     * @param  string $element
     * @return $this
     */
    protected function attach($absolutePath, $element)
    {
        $this->uploads[$element] = $absolutePath;
        return $this->storeInput($element, $absolutePath);
    }

    /**
     * Submit a form using the button with the given text value.
     *
     * @param  string $buttonText
     * @return $this
     */
    protected function press($buttonText)
    {
        return $this->submitForm($buttonText, $this->inputs, $this->uploads);
    }

    /**
     * Submit a form on the page with the given input.
     *
     * @param  string $buttonText
     * @param  array $inputs
     * @param  array $uploads
     * @return $this
     */
    protected function submitForm($buttonText, $inputs = [], $uploads = [])
    {
        $this->makeRequestUsingForm($this->fillForm($buttonText, $inputs), $uploads);
        return $this;
    }

    /**
     * Fill the form with the given data.
     *
     * @param  string $buttonText
     * @param  array $inputs
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function fillForm($buttonText, $inputs = [])
    {
        if (!is_string($buttonText)) {
            $inputs = $buttonText;
            $buttonText = null;
        }
        return $this->getForm($buttonText)->setValues($inputs);
    }

    /**
     * Get the form from the page with the given submit button text.
     *
     * @param  string|null $buttonText
     * @return \Symfony\Component\DomCrawler\Form
     *
     * @throws \InvalidArgumentException
     */
    protected function getForm($buttonText = null)
    {
        try {
            if ($buttonText) {
                return $this->crawler->selectButton($buttonText)->form();
            }
            return $this->crawler->filter('form')->form();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Could not find a form that has submit button [{$buttonText}].");
        }
    }

    /**
     * Store a form input in the local array.
     *
     * @param  string $element
     * @param  string $text
     * @return $this
     */
    protected function storeInput($element, $text)
    {
        $this->assertFilterProducesResults($element);
        $element = str_replace('#', '', $element);
        $this->inputs[$element] = $text;
        return $this;
    }

    /**
     * Assert that a filtered Crawler returns nodes.
     *
     * @param  string $filter
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function assertFilterProducesResults($filter)
    {
        $crawler = $this->filterByNameOrId($filter);
        if (!count($crawler)) {
            throw new InvalidArgumentException("Nothing matched the filter [{$filter}] CSS query provided for [{$this->currentUri}].");
        }
    }

    /**
     * Filter elements according to the given name or ID attribute.
     *
     * @param  string $name
     * @param  array|string $elements
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function filterByNameOrId($name, $elements = '*')
    {
        $name = str_replace('#', '', $name);
        $id = str_replace(['[', ']'], ['\\[', '\\]'], $name);
        $elements = is_array($elements) ? $elements : [$elements];
        array_walk($elements, function (&$element) use ($name, $id) {
            $element = "{$element}#{$id}, {$element}[name='{$name}']";
        });
        return $this->crawler->filter(implode(', ', $elements));
    }

    /**
     * TODO
     *
     * @param $error
     * @param array $array
     * @param string $tmp_name
     * @param string $name
     * @param string $type
     * @param string $size
     * @return array
     */
    private function buildFile($error, &$array = array(), $tmp_name = '', $name = '', $type = '', $size = '')
    {
        if ($tmp_name !== '' && $name === '') {
            $name = basename($tmp_name);
        }
        if ($tmp_name !== '' && $type === '') {
            $type = mime_content_type($tmp_name);
        }
        if ($tmp_name !== '' && $size === '') {
            $size = filesize($tmp_name);
        }
        if (isset($array['error'])) {
            $array['error'][] = $error;
            $array['name'][] = $name;
            $array['tmp_name'][] = $tmp_name;
            $array['type'][] = $type;
            $array['size'][] = $size;
        } else {
            $array['error'] = $error;
            $array['name'] = $name;
            $array['tmp_name'] = $tmp_name;
            $array['type'] = $type;
            $array['size'] = $size;
        }
        return $array;
    }
}
