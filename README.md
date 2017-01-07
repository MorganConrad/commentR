# commentR
Basic RESTful comment system for web pages, allowing easy anonymous comments.


## Test it out

 1. Download this code into a folder
 2. Start a webserver
 3. Goto localhost/path/to/thatFolder/demo.html

## Installation

 1. Download this project
 2. (optional) Edit the `defines` in commentR.php
 3. Edit `var rootURL = "http://localhost/commentR.php";` in commentR.js to point to your host.
 4. On your web pages
    1. Add `<script src="commentR.js"></script>`.
    2. Add a `<div id="someID">` where you want comments.
    3. Add `<body onload="loadComments1('unique_ID', 'someID');">` where 'uniqueID' uniquely IDs that webpage.  (`window.location.pathname` is a simple choice.)

Note:  /db/initialdb.db3 is a clean, empty SQLite3 DB to use to "reinitialize" should you corrupt the DB.

## Docs

### RESTful Backend uses PHP and SQLite3

  1. Use GET or POST to http://yourserver.com/commentR.php/unique/path
      - Typically, you will have commentR.php on the same host, but this is not required.  
      - Note: if on a separate host, or it is supporting multiple web sites, be careful about your unique IDs.
      - /unique/path can be whatever you want, but easiest is your URL or (simpler) just the path part (or permalink).
  2. The SQLite3 has two tables:
    - `permalinks` table has IDs for each unique path.
    - `comments` table has a FK so you can get all comments for that permalink.
  4. There are some **minimal** attempts to validate comments, prevent spam, etc.

### Example Frontend code at [commentR.js](https://github.com/MorganConrad/commentR/blob/master/commentR.js) and [demo.html](https://github.com/MorganConrad/commentR/blob/master/demo.html)

  This uses

    <body onload="loadComments1('unique_ID', 'comments');">

 - we use window.location.pathname 'unique_ID'.
 - 'comments' is the **#id** of `<div>` into which the comment section will be inserted.  
 - All the comment boxes have CSS classes, but there is no .css file provided, so formatting is primitive but usable, or roll your own.  


### Alternatives

After starting on this, I found several alternatives, which are more complete and likely preferable.  You should probably look at them.

#### [HashOver](http://tildehash.com/?page=hashover)

PHP Comment System, PHP, self-hosted, comments stored as XML files.


#### [Isso](https://posativ.org/isso/)

Python, comments stored in SQLite3


#### [commentary](https://github.com/sdqali/commentary)

Ruby, inactive


#### Unmaintained systems
 - [talkatv](https://github.com/talkatv/talkatv)  Python
 - [juvia](https://github.com/phusion/juvia)  Ruby


#### [More CMSs on Wikipedia](https://en.wikipedia.org/wiki/List_of_content_management_systems)
