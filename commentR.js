var rootURL = "http://localhost/commentR.php";

function loadComments1(myPath, id) {
   var commentDiv = document.getElementById(id);
   var xmlHttp = new XMLHttpRequest();
   xmlHttp.onreadystatechange = function() {
      if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
         loadComments2(commentDiv, xmlHttp.responseText);
         
         form = createForm(myPath);
         commentDiv.appendChild(form);
      }
   };
   
   xmlHttp.open("GET", rootURL + myPath, true); // true for asynchronous
   xmlHttp.send(null);
}


function loadComments2(commentsDiv, responseText) {
   var comments = JSON.parse(responseText);
   comments.forEach(function (comment) {
      var div1 = createDiv("commentR-div1");
      
      var textTA = createTextArea(50,4);
      textTA.className = "commentR-text";
      textTA.value = comment.text;
      div1.className = "commentR-text";
      
      var div2 = createDiv("commentR-div2");
      
      var nameTA = createTextArea(24);
      nameTA.className = "commentR-name";
      nameTA.value = comment.name || '';
      
      var emailTA = createTextArea(24);
      emailTA.className = "commentR-email";
      emailTA.value = comment.email || '';
      
      div1.appendChild(textTA);
      div2.appendChild(nameTA);
      div2.appendChild(emailTA);
      
      commentsDiv.appendChild(div1);
      commentsDiv.appendChild(div2);
      
      var date = new Date(comment.date*1000);
      if (!isNaN( date.getTime() )) {
         var div3 = createDiv("commentR-div3");
         var when = document.createTextNode("@ " + date.toUTCString());
         div3.appendChild(when);
         commentsDiv.appendChild(div3);
      }
      
   });
}


function createForm(myPath, formName) {
   formName = formName || 'commentForm';
   
   var form = document.createElement("form");
   form.className = 'commentR-form';
   form.id = formName;
   form.setAttribute('name', formName);
   form.setAttribute('method',"post");
   form.setAttribute('action',"/commentR.php" + myPath);
   
   var text = createTextArea(50, 4, true, 'text', formName);
   text.title = text.placeholder = "Type your comment here";
   var name = createInput('name', null, {placeholder: 'John Doe', title:"Your Name (optional)"});
   var email = createInput('email', 'email', {placeholder: 'john@email.com', title:"Your Email (optional)"});
   var submit = createInput('submit', 'submit');  //createInput("submit", "submit");
   submit.innerHTML="Submit";
   
   var div1 = createDiv("commentR-div1");
   var div2 = createDiv("commentR-div2");
   
   div1.appendChild(text);
   div2.appendChild(name);
   div2.appendChild(email);
   div2.appendChild(submit);
   
   form.appendChild(div1);
   form.appendChild(div2);
   
   var time = createHidden('time', (new Date().getTime() / 1000));
   form.appendChild(time);  
   var format = createHidden('format', 'HTML');
   form.appendChild(format);
   
   return form;
}



function createInput(name, type, opts) {
   opts = opts || {};
   var input = document.createElement("input");

   input.name = name;
   if (opts.placeholder)
      input.placeholder = opts.placeholder;
   if (opts.title)
      input.title = opts.title;
   
   if (type)
      input.type = type;

   return input;
}


function createHidden(name, value) {
   var hidden = createInput(name, 'hidden');
   hidden.value = value;
   hidden.visibility = false;
   return hidden;
}



function createTextArea(cols, rows, editable, name, form) {
   var ta = document.createElement("textarea");
   ta.cols = cols;  
   ta.rows = rows || 1;
   ta.readonly = !editable;
   
   if (name)
      ta.setAttribute('name', name);
   if (form)
      ta.setAttribute('form', form);
   
   return ta;
}

function createDiv(className) {
   var div = document.createElement("div");
   if (className)
      div.className = className;
   
   return div;
}