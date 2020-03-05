# HumHub ONLYOFFICE integration plugin

This plugin enables users to edit office documents from [HumHub](https://www.humhub.com/) using ONLYOFFICE Document Server - [Community or Integration Edition](#onlyoffice-document-server-editions).

## Features

The plugin allows to:

* Create and edit text documents, spreadsheets, and presentations.
* Share documents with other users.
* Co-edit documents in real-time: use two co-editing modes (Fast and Strict), Track Changes, comments, and built-in chat.

Supported formats:

* For viewing and editing:  DOCX, XLSX, PPTX.
* For viewing only: ODT, ODS, ODP, DOC, XLS, PPT, TXT, PDF.
* For conversion to Office Open XML: ODT, ODS, ODP, DOC, XLS, PPT, TXT, CSV.

## Installing ONLYOFFICE Document Server

You will need an instance of ONLYOFFICE Document Server that is resolvable and connectable both from HumHub and any end clients. ONLYOFFICE Document Server must also be able to POST to HumHub directly.

You can install free Community version of ONLYOFFICE Document Server or scalable enterprise-level Integration Edition.

To install free Community version, use [Docker](https://github.com/onlyoffice/Docker-DocumentServer) (recommended) or follow [these instructions](https://helpcenter.onlyoffice.com/server/linux/document/linux-installation.aspx) for Debian, Ubuntu, or derivatives.  

To install Integration Edition, follow instructions [here](https://helpcenter.onlyoffice.com/server/integration-edition/index.aspx).

Community Edition vs Integration Edition comparison can be found [here](#onlyoffice-document-server-editions).

## Installing HumHub ONLYOFFICE integration plugin

Either install it from HumHub Marketplace or simply clone the repository inside one of the folder specified by `moduleAutoloadPaths` parameter. Please see [HumHub Documentation](http://docs.humhub.org/dev-environment.html#external-modules-directory) for more information.

## Configuring HumHub CONLYOFFICE integration plugin

Navigate to `Administration` -> `Modules` find the plugin under Installed tab and click `Configure`.

## How it works

The ONLYOFFICE integration follows the API documented [here](https://api.onlyoffice.com/editors/basic):

* When creating a new file, the user will be provided with Document, Spreadsheet or Presentation options in the `Create document` menu.

* The browser invokes the `index` method in the `/controllers/CreateController.php` controller.

* Or, when opening an existing file, the user will be provided with `View document` or `Edit document` depending on an extension.

* A popup is opened and the `index` method of the `/controllers/OpenController.php` controller is invoked.

* The app prepares a JSON object with the following properties:

  * **url** - the URL that ONLYOFFICE Document Server uses to download the document;
  * **callbackUrl** - the URL that ONLYOFFICE Document Server informs about status of the document editing;
  * **key** - the random MD5 hash to instruct ONLYOFFICE Document Server whether to download the document again or not;
  * **title** - the document Title (name);
  * **id** - the identification of the user;
  * **name** - the name of the user.

* HumHub takes this object and constructs a page from `views/open/index.php` template, filling in all of those values so that the client browser can load up the editor.

* The client browser makes a request for the javascript library from ONLYOFFICE Document Server and sends ONLYOFFICE Document Server the DocEditor configuration with the above properties.

* Then ONLYOFFICE Document Server downloads the document from HumHub and the user begins editing.

* ONLYOFFICE Document Server sends a POST request to the _callbackUrl_ to inform HumHub that a user is editing the document.

* When all users and client browsers are done with editing, they close the editing window.

* After [10 seconds](https://api.onlyoffice.com/editors/save#savedelay) of inactivity, ONLYOFFICE Document Server sends a POST to the _callbackUrl_ letting HumHub know that the clients have finished editing the document and closed it.

* HumHub downloads the new version of the document, replacing the old one.

## ONLYOFFICE Document Server editions

ONLYOFFICE offers different versions of its online document editors that can be deployed on your own servers.

**ONLYOFFICE Document Server:**

* Community Edition (`onlyoffice-documentserver` package)
* Integration Edition (`onlyoffice-documentserver-ie` package)

The table below will help you make the right choice.

| Pricing and licensing | Community Edition | Integration Edition |
| ------------- | ------------- | ------------- |
| | [Get it now](https://www.onlyoffice.com/download.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  | [Start Free Trial](https://www.onlyoffice.com/connectors-request.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  |
| Cost  | FREE  | [Go to the pricing page](https://www.onlyoffice.com/integration-edition-prices.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  |
| Simultaneous connections | up to 20 maximum  | As in chosen pricing plan |
| Number of users | up to 20 recommended | As in chosen pricing plan |
| License | GNU AGPL v.3 | Proprietary |
| **Support** | **Community Edition** | **Integration Edition** | 
| Documentation | [Help Center](https://helpcenter.onlyoffice.com/server/docker/opensource/index.aspx) | [Help Center](https://helpcenter.onlyoffice.com/server/integration-edition/index.aspx) |
| Standard support | [GitHub](https://github.com/ONLYOFFICE/DocumentServer/issues) or paid | One year support included |
| Premium support | [Buy Now](https://www.onlyoffice.com/support.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub) | [Buy Now](https://www.onlyoffice.com/support.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub) |
| **Services** | **Community Edition** | **Integration Edition** | 
| Conversion Service                | + | + | 
| Document Builder Service          | + | + | 
| **Interface** | **Community Edition** | **Integration Edition** |
| Tabbed interface                       | + | + |
| White Label                            | - | - |
| Integrated test example (node.js)     | - | + |
| **Plugins & Macros** | **Community Edition** | **Integration Edition** |
| Plugins                           | + | + |
| Macros                            | + | + |
| **Collaborative capabilities** | **Community Edition** | **Integration Edition** |
| Two co-editing modes              | + | + |
| Comments                          | + | + |
| Built-in chat                     | + | + |
| Review and tracking changes       | + | + |
| Display modes of tracking changes | + | + |
| Version history                   | + | + |
| **Document Editor features** | **Community Edition** | **Integration Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Adding Content control          | - | + | 
| Editing Content control         | + | + | 
| Layout tools                    | + | + |
| Table of contents               | + | + |
| Navigation panel                | + | + |
| Comparing Documents             | - | +* |
| **Spreadsheet Editor features** | **Community Edition** | **Integration Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Functions, formulas, equations  | + | + |
| Table templates                 | + | + |
| Pivot tables                    | +** | +** |
| **Presentation Editor features** | **Community Edition** | **Integration Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Animations                      | + | + |
| Presenter mode                  | + | + |
| Notes                           | + | + |
| | [Get it now](https://www.onlyoffice.com/download.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  | [Start Free Trial](https://www.onlyoffice.com/connectors-request.aspx?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  |

\* It's possible to add documents for comparison from your local drive and from URL. Adding files for comparison from storage is not available yet.

\** Changing style and deleting (Full support coming soon)
