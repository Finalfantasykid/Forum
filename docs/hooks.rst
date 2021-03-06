.. index:: single: Custom Hooks

Custom Hooks
============

The Forum implements several custom hooks to allow for a more
customizable environment. For more information on built in mediawiki
hooks, go to `MediaWiki Manual`_. The custom hooks are:

TopLevelTabs
------------

**Description**

This hook is called to populate the top level tabs. Typically in the
implemented function it will call the TabUtils::createTab function.

**Define Function**

.. code:: php
    
    function createTab($tabs) { ... }
    
SubLevelTabs
------------

**Description**

This hook is called to populate the second level tabs. Typically in the
implemented function it will call the TabUtils::createSubTab function
and append the subtab to one of the top level tabs

**Define Function**

.. code:: php

    function createSubTabs($tabs) { ... }

ToolboxHeaders
--------------

**Description**

This hook is called to populate the headers in the toolbox. Typically in
the implemented function it will call the TabUtils::createToolboxHeader
function.

**Define Function**

.. code:: php

    function createToolboxHeader($toolbox) { ... }

ToolboxLinks
------------

**Description**

This hook is called to populate the links under the headers in the
toolbox. Typically in the implemented function it will call the
TabUtils::createToolboxLink function and append the link to one of the
toolbox headers.

**Define Function**

.. code:: php

    function createToolboxLinks($toolbox) { ... }

CheckImpersonationPermissions
-----------------------------

**Description**

This is called when a person is trying to impersonate someone else.
Normally a user will not have permissions to see any page on the forum
as another user (unless they are a manager), however by calling this
hook it a page can override this with its own access control logic.

**Define Function**

.. code:: php

    function checkImpersonationPermissions($person, $realPerson, $ns, $title, $pageAllowed) { ... }

ImpersonationMessage
--------------------

**Description**

When a user is impersonating someone a message is displayed to them. It
is a pretty generic message so calling this hook can be used to override
the message if it needs to be more specific.

**Define Function**

.. code:: php

    function impersonationMessage($person, $realPerson, $ns, $title, $message) { ... }

.. _MediaWiki Manual: http://www.mediawiki.org/wiki/Manual:Hooks
