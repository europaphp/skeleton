<?php

/**
 * @author Trey Shugart <treshugart@gmail.com>
 * @date 2010-02-13
 * @license BSD http://www.opensource.org/licenses/bsd-license.php
 * Copyright (c) 2010, Trey Shugart
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * 
 *  - Redistributions of source code must retain the above copyright notice, this list of 
 *    conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice, this list 
 *    of conditions and the following disclaimer in the documentation and/or other materials 
 *    provided with the distribution.
 *  - Neither pQuery nor the names of its contributors may be used to endorse or promote 
 *    products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL 
 * THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT 
 * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR 
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @package pQuery
 */
class pQuery implements Iterator
{
	/**
	 * The current selector being used.
	 * 
	 * @var string
	 */
	protected $selector = null;
	
	/**
	 * The current context selector being used.
	 * 
	 * @var string
	 */
	protected $context = null;
	
	/**
	 * An array of selected DOMNode objects.
	 * 
	 * @var string
	 */
	protected $nodeList = array();
	
	/**
	 * Whether or not a doctype was explicitly used.
	 * 
	 * @var string
	 */
	protected $hasDoctype = false;
	
	/**
	 * Whether or not an html tag was explicitly used.
	 * 
	 * @var string
	 */
	protected $hasHtmlTag = false;
	
	/**
	 * Whether or not a body tag was explicitly used.
	 * 
	 * @var string
	 */
	protected $hasBodyTag = false;
	
	/** 
	 * Used to represent the iterator index.
	 * 
	 * @var int
	 */
	private $index = 0;
	
	/**
	 * Constructs a new pQuery object and effectively handles nearly any passed in
	 * $document argument.
	 * 
	 * @param pQuery|DOMDocument|DOMNode|DOMNodeList|array|string $document
	 * @param string $selector
	 * @param string $context
	 * @return pQuery
	 */
	public function __construct($document = null, $selector = null, $context = null)
	{
		// handle pQuery instances, DOMDocument istances, URIs, files and strings
		if ($document instanceof pQuery) {
			$this->selector   = $document->selector;
			$this->context    = $document->context;
			$this->nodeList   = $document->nodeList;
			$this->hasDoctype = $document->hasDoctype;
			$this->hasHtmlTag = $document->hasHtmlTag;
			$this->hasBodyTag = $document->hasBodyTag;
		} elseif ($document instanceof DOMDocument) {
			foreach ($document->childNodes as $node) {
				$this->nodeList[] = $node;
			}
		} elseif ($document instanceof DOMNode) {
			$this->nodeList[] = $document;
		} elseif ($document instanceof DOMNodeList) {
			foreach ($document as $node) {
				$this->nodeList[] = $node;
			}
		} elseif (is_array($document)) {
			$this->nodeList = $document;
		} else {
			// handle a url, file or html string
			$document = preg_match('/^http:\/\//', $document) || is_file($document)
				? file_get_contents($document)
				: $document;
			
			// create a new document to import the string to
			$doc = new DOMDocument;
			
			// supress errors to load any type of html including text nodes
			@$doc->loadHTML($document);
			
			// import nodes
			foreach ($doc->childNodes as $node) {
				$this->nodeList[] = $node;
			}
			
			// set flags
			$this->hasDoctype = (bool) preg_match('#<!DOCTYPE#i', $document);
			$this->hasHtmlTag = (bool) preg_match('#<html#i', $document);
			$this->hasBodyTag = (bool) preg_match('#<body#i', $document);
		}
		
		// set properties
		$this->selector = $selector;
		$this->context  = $context;
		
		if ($this->selector) {
			// retrieve a new document while keeping references
			$doc = $this->getDocument($this->nodeList);
			
			// to run queries against
			$xPath = new DOMXPath($doc);
			
			// find the context node/element
			$this->context = @$xPath->query($this->context);
			
			// set the filtered node list
			if ($this->context) {
				$nodeList = $xPath->query($this->selector, $this->context->item(0));
			} else {
				$nodeList = $xPath->query($this->selector);
			}
			
			// reset the node list
			$this->nodeList = array();
			
			// re-import the filtered nodes
			foreach ($nodeList as $node) {
				$this->nodeList[] = $node;
			}
		}
	}
	
	/**
	 * Provides a magical way to cal pQuery->html().
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->html();
	}
	
	/**
	 * Acts as a getter for the protected properties rendering them readonly
	 * since there is no __set method defined to set them.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if ($name === 'length') {
			return $this->size();
		}
		
		return $this->$name;
	}
	
	/**
	 * Used to set the length of the array. The array is sliced starting from
	 * offset 0 to whatever the length is being set to.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ($name === 'length' && is_numeric($value)) {
			$this->size($value);
		}
	}
	
	/**
	 * Returns an element at the specified index or false if not found. If
	 * no index is specified then an array of the current raw elements is
	 * returned. If no elements are in the current list, an empty array is
	 * returned.
	 * 
	 * @param int $index
	 * @return DOMNode|array|false
	 */
	public function get($index = null)
	{
		// if an index is specified
		if (is_numeric($index)) {
			if (!isset($this->nodeList[$index])) {
				return null;
			}
			
			return $this->nodeList[$index];
		}
		
		// return the whole node list
		return $this->nodeList;
	}
	
	/**
	 * Returns the element in the node list at the given index as a pQuery
	 * object. If no element is found, false is returned.
	 * 
	 * If no index is passed, an array of pQuery objects representing each
	 * element in the node list is returned. If the list is empty, then an
	 * empty array is returned.
	 * 
	 * @param int $index
	 * @return pQuery|array|false
	 */
	public function eq($index = null)
	{
		// return the specified index as a pQuery object if specified
		if (is_numeric($index)) {
			$el = $this->get($index);

			if (!$el) {
				return false;
			}
			
			return new self($el);
		}
		
		$list = array();
		
		// build the list of pQuery objects to return
		foreach ($this->nodeList as $node) {
			$list[] = new self($node);
		}
		
		return $list;
	}
	
	/**
	 * Finds the nodes matching the given expression using the current node list
	 * as the context.
	 * 
	 * @param string $expression
	 * @return pQuery
	 */
	public function find($expression)
	{
		return new self($this, $expression);
	}
	
	/**
	 * Returns the html (outer) of all of the matched elements in the current 
	 * node list. If $html is passed in, then the current node list is replaced
	 * by the passed html.
	 * 
	 * If this includes more than one node, each elements outer html is 
	 * concatenated and output. This can be useful for example joining list
	 * items from different lists into one list.
	 * 
	 * @param string $html
	 * @return string
	 */
	public function html($html = null)
	{
		if (is_null($html)) {
			// if there are no nodes, return null
			if (!$this->get()) {
				return '';
			}
			
			// create a new document from the current nodes
			$str = $this->getDocument($this->nodeList)->saveHTML();
			
			// remove automated doctype if it wasn't explicitly typed
			if (!$this->hasDoctype) {
				$str = preg_replace('#<!DOCTYPE([^>]*)>#i', '', $str);
			}
			
			// remove automated html tag if it wasn't explicitly typed
			if (!$this->hasHtmlTag) {
				$str = preg_replace('#</?html([^>]*)>#i', '', $str);
			}
			
			// remove automated body tag if it wasn't explicitly typed
			if (!$this->hasBodyTag) {
				$str = preg_replace('#</?body([^>]*)>#i', '', $str);
			}
			
			// normalize and return
			return trim($str);
		} else {
			// create a object from the html
			$html = new self($html);
			
			// copy the imported html
			foreach ($html->nodeList as $node) {
				$this->nodeList[] = $node;
			}
		}
		
		return $this;
	}
	
	/**
	 * Returns the text content of the first matched element.
	 * 
	 * @return string
	 */
	public function text()
	{
		return $this->get(0)->textContent;
	}
	
	/**
	 * Returns the size of the current node list. If a length is passed in
	 * then the array is sized to that length starting from offset 0. Either
	 * way, the size of the node list is returned. Note that you cannot
	 * extend the node list, only truncate it.
	 * 
	 * @param int $length
	 * @return int
	 */
	public function size($length = null)
	{
		if (is_numeric($length)) {
			$this->nodeList = array_slice($this->nodeList, 0, (int) $length);
		}
		
		return count($this->nodeList);
	}
	
	/**
	 * Extracts part of the selected nodes.
	 * 
	 * @param int $start The start index to start slicing from.
	 * @param int $length The number of items to slice out.
	 * @return pQuery
	 */
	public function slice($start, $len = null)
	{
		$items = array_slice($this->nodeList, $start, $len);
		
		return new pQuery($items);
	}
	
	/**
	 * Returns the parent element of the first matched element in the
	 * current node list. If we are already at the topmost element
	 * false is returned.
	 * 
	 * @return pQuery|false
	 */
	public function parent()
	{
		$el = $this->get(0);
		
		if ($el && $el->parentNode) {
			return new self($el->parentNode);
		}
		
		return false;
	}
	
	/**
	 * Returns the first children of the first matched node. If no
	 * children are found, then false is returned.
	 * 
	 * @return pQuery|false
	 */
	public function children()
	{
		$el = $this->get(0);
		
		if ($el && $el->childNodes && $el->childNodes->length) {
			return new self($this->childNodes);
		}
		
		return false;
	}
	
	/**
	 * Appends the passed in nodes to the current list.
	 * 
	 * @param mixed $html
	 * @return pQuery
	 */
	public function append($html)
	{
		$html = new self($html);
		
		$this->nodeList[] = $html;
		
		return $this;
	}
	
	/**
	 * Appends the current node list to the passed in list and returns
	 * the new list.
	 * 
	 * @param mixed $html
	 * @return pQuery
	 */
	public function appendTo($html)
	{
		$html = new self($html);
		
		return $html->append($this);
	}
	
	/**
	 * Prepends the passed in nodes to the current node set.
	 * 
	 * @param mixed $html
	 * @return pQuery
	 */
	public function prepend($html)
	{
		$html = new self($html);
		
		array_unshift($this->nodeList, $html->nodeList);
		
		return $this;
	}

	/**
	 * Prepends the current nodes to the passed in node list and returns
	 * the new list.
	 * 
	 * @param mixed $html
	 * @return pQuery
	 */
	public function prependTo($html)
	{
		$html = new self($html);
		
		return $html->prepend($this);
	}

	/**
	 * Clones the selected list of nodes and returns a new pQuery object.
	 * 
	 * @return pQuery
	 */
	public function copy()
	{
		$nodes = array();
		
		foreach ($this->nodeList as $node) {
			$nodes[] = $node->cloneNode(true);
		}
		
		return new self($nodes);
	}

	/**
	 * Removes the selected list of nodes.
	 * 
	 * @return pQuery
	 */
	public function remove()
	{
		$this->nodeList = array();

		return $this;
	}

	/**
	 * Sets or retrieves an attribute value.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function attr($name, $value = null)
	{
		$el = $this->get(0);

		if (is_null($value)) {
			if (!$el) {
				return null;
			}
			
			return $el->getAttribute($name);
		}

		if ($el) {
			$el->setAttribute($name, (string) $value);
		}

		return $this;
	}
	
	/**
	 * Creates a DOMDocument object from the current node list and returns it.
	 * 
	 * @return DOMDocument
	 */
	public function getDocument($nodes = array())
	{
		// make sure it's an array
		if (!is_array($nodes)) {
			$nodes = array($nodes);
		}
		
		// document implementation for doctype and document creation
		$implement = new DOMImplementation;
		
		if ($nodes) {
			$firstNode = $nodes[0];
			
			/*
			 * If there is a doctype associated to the document, use it.
			 * 
			 * Only the first element can be a doctype.
			 */
			if ($firstNode instanceof DOMDocumentType) {
				$dtd = $implement->createDocumentType($firstNode->name, $firstNode->publicId, $firstNode->systemId);
				$doc = $implement->createDocument(null, null, $dtd);
			} else {
				$doc = $implement->createDocument();
			}
			
			// import only valid nodes from the node list
			foreach ($nodes as $node) {
				if ($node instanceof DOMElement) {
					$test = $doc->appendChild($doc->importNode($node, true));
				}
			}
			
			return $doc;
		}
		
		return $implement->createDocument();
	}
	
	/**
	 * Returns the current element in the iteration.
	 * 
	 * @return pQuery
	 */
	public function current()
	{
		return $this->eq($this->index);
	}
	
	/**
	 * Increments the internal pointer.
	 * 
	 * @return void
	 */
	public function next()
	{
		++$this->index;
	}
	
	/**
	 * Returns the index of the internal pointer.
	 * 
	 * @return int
	 */
	public function key()
	{
		return $this->index;
	}
	
	/**
	 * Resets the internal pointer.
	 * 
	 * @return void
	 */
	public function rewind()
	{
		$this->index = 0;
	}
	
	/**
	 * Checks to see whether it is valid to continue iteration.
	 * 
	 * @return bool
	 */
	public function valid()
	{
		return $this->index < $this->size();
	}
}