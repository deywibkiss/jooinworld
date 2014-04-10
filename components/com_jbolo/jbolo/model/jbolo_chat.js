var jb_originalTitle;
var jb_windowFocus = true;
var msgsuc = 0;
var cuser;
var chatBoxes = new Array();
var me_avtr;
var nodeid_count = new Array();
chat_history = gsCookie("jbolo_chat_history");
cookie = gsCookie("jbolo_mini");
close_cookie = gsCookie("jbolo_close");
open_cookie = gsCookie("jbolo_open");
var log_username;
var jbolo_chat_history = chat_history ? chat_history.split("|") : [];
var jbolo_mini = cookie ? cookie.split("|") : [];
var jbolo_close = close_cookie ? close_cookie.split("|") : [];
var jbolo_open = open_cookie ? open_cookie.split("|") : [];
var old_list = [];
var poll_list = [];
old_list = poll_list;
var rem = [];
var userdetail = [];
var u_name = [];
var user_stat = [];
var st_message = [];
var photo = [];
var close_flag = 1;
var template1;
var smilehtml; /*manoj*/
var jb_chatHeartbeatCount = 3;
var wasted_minutes = 0;
var interval_period = 60000;
var jb_chatHeartbeatTime = jb_minChatHeartbeat;

function gsCookie(name, value, options) {
	if (typeof value != 'undefined') { /* name and value given, set cookie*/
		options = options || {};
		if (value === null) {
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if (typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			} else {
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString(); /* use expires attribute, max-age is not supported by IE*/
		} /* CAUTION: Needed to parenthesize options.path and options.domain*/
		/* in the following expressions, otherwise they evaluate to undefined*/
		/* in the packed version for some reason...*/
		var path = options.path ? '; path=' + (options.path) : '';
		var domain = options.domain ? '; domain=' + (options.domain) : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	} else { /* only name given, get cookie*/
		var cookieValue = null;
		if (document.cookie.length > 0 && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for (var i = 0; i < cookies.length; i++) {
				var cookie = techjoomla.jQuery.trim(cookies[i]); /* Does this cookie string begin with the name we want?*/
				if (cookie.substring(0, name.length + 1) == (name + '=')) {
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		return cookieValue;
	}
}

/* Define a new array method that return a unique array for given array.
 **/
Array.prototype.getUnique = function () {
	var a = [],
		o = {},
		i, e;
	for (i = 0; e = this[i]; i++) {
		o[e] = 1
	};
	for (e in o) {
		a.push(e)
	};
	return a;
}
/*Fix indexOf in IE*/
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function (obj, start) {
		for (var i = (start || 0), j = this.length; i < j; i++) {
			if (this[i] == obj) {
				return i;
			}
		}
		return -1;
	}
}

/* This - returns unique array for given array
 * @param array arrayName
 * @return array newArray
 **/
function jbunique(arrayName) {
	var newArray = new Array();
	label: for (var i = 0; i < arrayName.length; i++) {
		for (var j = 0; j < newArray.length; j++) {
			if (newArray[j] == arrayName[i]) continue label;
		}
		newArray[newArray.length] = arrayName[i];
	}
	return newArray;
}

/* Checks if given element is present in array
 * * @param arr array e.g. ["776_1", "776_2"]
 * @param arr element e.g. 776_3
 * @return 1 OR 0
 **/
function include(arr, obj) {
	for (var i = 0; i < arr.length; i++) {
		if (arr[i] == obj) {
			return 1;
		}
	}
	return 0;
}

function jq(myid) {
	return '#' + myid.replace(/(:|\.)/g, '\\techjoomla.jQuery1');
}

techjoomla.jQuery(document).ready(function () {
	/*window title*/
	jb_originalTitle=document.title;
	techjoomla.jQuery([window, document]).blur(function () {
		jb_windowFocus = false;
	}).focus(function () {
		jb_windowFocus = true;
		document.title = jb_originalTitle;
	});
	/* code for the user session*/
	interval_pointer = setInterval(timer_handler, interval_period);
	jQuery(document).bind('keypress mousemove', function (event) {
		wasted_minutes = 0;
		jb_chatHeartbeatTime = jb_minChatHeartbeat; /* Reset hearbeat time after user gets active again */
	});
	/* end of code*/
	techjoomla.jQuery(document).mouseup(function (e) {
		var container = techjoomla.jQuery(".extstatuslist");
		if (container.has(e.target).length === 0) {
			container.hide();
		}
	});
	techjoomla.jQuery(document).mouseup(function (e) {
		var container = techjoomla.jQuery(".updatedstatus");
		if (container.has(e.target).length === 0) {
			container.hide();
		}
	});
});

/* insert character(s) at selected cursor location
 * http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery/946556#946556
 **/
techjoomla.jQuery.fn.insertAtCaret = function (myValue) {
	return this.each(function () { /*IE support*/
		if (document.selection) {
			this.focus();
			sel = document.selection.createRange();
			sel.text = myValue;
			this.focus();
		} /*MOZILLA/NETSCAPE support*/
		else if (this.selectionStart || this.selectionStart == '0') {
			var startPos = this.selectionStart;
			var endPos = this.selectionEnd;
			var scrollTop = this.scrollTop;
			this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
			this.focus();
			this.selectionStart = startPos + myValue.length;
			this.selectionEnd = startPos + myValue.length;
			this.scrollTop = scrollTop;
		} else {
			this.value += myValue;
			this.focus();
		}
	});
};

/* This - initializes live-click for class - addpeople FOR toggle
 **/
techjoomla.jQuery(".addpeople").live('click', function () {
	var id = techjoomla.jQuery(this).attr('id');
	/*addusertoggle(id);*/
	if (addusertoggle(id)) {
		var chatWindow = techjoomla.jQuery(this).parent().parent().parent().parent().parent();
		var chatWindowContainer = chatWindow.parent();
		/*techjoomla.jQuery(this).parent().parent().css('margin', '45px 5px 5px');*/
	}
});

/* This - initializes live-click for class - add_users FOR autocomplete
 * - Used when adding a new user for group chat
 **/
var countcall = 1;
techjoomla.jQuery(".add_users").live('click', function () {
	if (countcall) {
		countcall = 1;
		var id = techjoomla.jQuery(this).attr('id');
		var pid = techjoomla.jQuery(this).parent().attr("id");
		var ppid = techjoomla.jQuery(this).parent().parent().parent().attr("id");
		/* IDs-All group chat user id array */
		var IDs = [];
		techjoomla.jQuery("#" + pid + " .plist ").find(".userdetails").each(function () {
			IDs.push(this.id);
		});
		/*maxChatUsers-Max group chat user supported*/
		if (IDs.length >= maxChatUsers) {
			techjoomla.jQuery("#" + ppid).find('.invite').attr("disabled", "disabled");
			alert(jbolo_lang['COM_JBOLO_GC_MAX_USERS']);
		} else {
			techjoomla.jQuery("#" + ppid).find('.invite').removeAttr("disabled");
		}
		if (IDs.length > 0) {
			autocomplete_users(id, IDs, 1);
		} else {
			autocomplete_users(id, IDs, techjoomla.jQuery("#" + ppid).find('#wt').text());
		}
	}
});

/* This function - initializes live-click for inviting user to group chat
 **/
techjoomla.jQuery('.invite').live('click', function () {
	var nid = techjoomla.jQuery(this).attr('id');
	var str = nid.split("_");
	var user_add = techjoomla.jQuery('#' + str[0] + '_addusers .add_users').val();
	if (user_add) {
		var inviteid = techjoomla.jQuery("#" + str[0] + '_addusers .inhd').val();
		if (inviteid) {
			/*empty addusers div value*/
			techjoomla.jQuery("#" + str[0] + '_addusers .add_users').val('');
			techjoomla.jQuery("#" + str[0] + '_addusers .inhd').val(''); /*hide addusers div*/
			addusertoggle(str[0]);
			init_groupchat(nid, inviteid, str[1]);
		}
	}
});

/*start added for jbolo - hd integration*/
function addActivityToTicket(nid) {
	var chatlog = jQuery('#chatbox_' + nid + ' .chatmessages').html();
	if (chatlog) {
		var r = confirm(jbolo_lang.COM_JBOLO_ADD_ACTIVITY_PROMPT_MSG);
		if (r == true) {
			/*chatlog = chatlog.replace(/(?:&nbsp;|<br>)/g,'<br />');*/
			window.open(site_link + "index.php?option=com_jbolo&tmpl=component&view=ticket&nid=" + nid + "&chatlog=" + chatlog, "mywindow", "menubar=1,resizable=1,width=600,height=500,scrollbars=1");
		} else {
			return 1;
		}
	} else {
		alert(jbolo_lang.COM_JBOLO_CHAT_WINDOW_EMPTY);
	}
} /*end added for jbolo - hd integration*/

/* This- toggles display for #3_addusers div
 * @param integer id e.g. 3 [usually nid]
 * */
function addusertoggle(id) {
	var visible = techjoomla.jQuery("#" + id + "_addusers").toggle().is(":visible");
	return visible;
}

/* This shows userlist for adding user to group chat.
 * @param string id e.g-add_1
 * @param array ids e.g-[1,2,3](already present user in group chat))
 * @param string wt e.g-vishnu(participant name)
 */
function autocomplete_users(id, ids, wt) {
	var cc = techjoomla.jQuery("#" + id).parent().attr('id');

	function split(val) {
		return val.split(/,\s*/);
	}

	function extractLast(term) {
		return split(term).pop();
	}
	techjoomla.jQuery("#" + id).bind("keydown", function (event) {}).autocomplete({
		minLength: 0,
		source: function (request, response) {
			techjoomla.jQuery.ajax({
				url: site_link + "index.php?option=com_jbolo&action=getAutoCompleteUserList",
				cache: false,
				type: 'POST',
				dataType: "json",
				data: {
					filterText: request.term
				},
				success: function (data) {
					var jboloError = 0;
					if (data.validate.error) {
						jboloError = data.validate.error;
					}
					if (jboloError === 1) {
						alert(data.validate.error_msg);
						return 0;
					}
					techjoomla.jQuery.each(data.userlist.users, function (i, users) {
						/*for showing users that not present in group chat.*/
						var m1 = [];
						for (i = 0; i < data.userlist.users.length; i++) {
							if (ids.length) {
								if (techjoomla.jQuery.inArray(data.userlist.users[i].uid, ids) > -1) {} else {
									m1.push({
										uname: data.userlist.users[i].uname,
										desc: data.userlist.users[i].uid
									});
								}
							} else if (wt != 1) {
								if (data.userlist.users[i].uname == wt) {} else {
									m1.push({
										uname: data.userlist.users[i].uname,
										desc: data.userlist.users[i].uid
									});
								}
							} else {
								m1.push({
									uname: data.userlist.users[i].uname,
									desc: data.userlist.users[i].uid
								});
							}
						} /*for showing userlist based on input text*/
						response(techjoomla.jQuery.map(m1, function (i) {
							return {
								label: i.uname,
								value: i.uname,
								desc: i.desc
							}
						}));
						/*response(techjoomla.jQuery.ui.autocomplete.filter(m1, extractLast(request.term)));*/
					});
				},
			});
		},
		focus: function () { /* prevent value inserted on focus*/
			return false;
		},
		select: function (event, ui) {
			var q = ui.item.desc;
			techjoomla.jQuery('#' + cc + ' .inhd').val(q); /*var st =q.split('_');*/
			var terms = split(this.value);
			this.value = '';
		}
	}); /*end of autocomplete*/

	function remove_li(thisli) {
		techjoomla.jQuery('#' + thisli).remove();
	};
}

/* This function - changes status & status-message for given user
 * @param int status e.g. 2
 * @param string msg e.g. hola chica
 * @param int uid e.g. 778
 * */
function changeStatus(sts, msg, uid) /*uid not used*/ {
	techjoomla.jQuery('#mestatus').removeClass();
	techjoomla.jQuery('#mestatus').addClass('statusicon_' + sts);
	techjoomla.jQuery("#extstatuslist").hide();
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=change_status",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			sts: sts,
			stsm: msg
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
		}
	});
}

/* This function - changes status message for given user
 * @param string msg e.g. hola chica
 * @param int uid e.g. 778
 **/
function changeStatusMsg(msg, uid) /*uid not used*/ {
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=change_status",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			stsm: msg
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
		}
	});
}

function chatFromAnywhere(uid, pid) {
	/*uid, pid, stcls e.g.: 794,793,statusicon_1*/
	var tnid = init_node(uid, pid, '');
}

/*Start added for jbolo-hd integration*/
function chatFromTicket(chatuser, ticketmask) {
	/*create or open a node*/
	var tnid = init_node(user_id, chatuser, '');
	/*push a message with ticket mask number to this node*/
	var msg = '{' + jbolo_lang.COM_JBOLO_TICKED_ID_NO_SPACE + '=' + ticketmask + '}';
	var ticketMaskPresent = jQuery("#chatbox_" + tnid + ":contains(" + msg + ")").html();
	if (ticketMaskPresent != null) {
		/*if ticket mask is present in chatwindow, highlight it*/
		/*techjoomla.jQuery("#chatbox_" + tnid + ":contains(" + msg + ")").css("text-decoration", "underline");*/
	} else {
		if (msg != '') {
			techjoomla.jQuery.ajax({
				type: 'POST',
				url: site_link + 'index.php?option=com_jbolo&action=pushChatToNode',
				async: false,
				data: {
					nid: tnid,
					msg: msg
				},
				dataType: 'json',
				success: function (data) {
					var jboloError = 0;
					if (data.validate.error) {
						jboloError = data.validate.error;
					}
					if (jboloError === 1) {
						alert(data.validate.error_msg);
						return 0;
					}
					updateChatHistory(tnid);
					message = data.pushChat_response.msg;
					techjoomla.jQuery.tmpl(document.getElementById("chatmessage"), {
						me: jbolo_lang.COM_JBOLO_ME,
						message: message,
						avtr: "'" + me_avtr + "'"
					}).appendTo("#chatbox_" + tnid + " .chatmessages");
				}
			});
		}
		setTimeout(function () { /*again strange bug so using timeout*/
			techjoomla.jQuery("#chatbox_" + tnid + " .chatmessages").scrollTop(techjoomla.jQuery("#chatbox_" + tnid + " .chatmessages").prop("scrollHeight") - techjoomla.jQuery("#chatbox_" + tnid + '.chatmessages').height());
		}, 300);
		jb_chatHeartbeatTime = jb_minChatHeartbeat;
		jb_chatHeartbeatCount = 1;
	}
	return true;
}
/*end added for jbolo-hd */

function chatHistory(nid) {
	myLink = site_link + "index.php?option=com_jbolo&tmpl=component&view=history&nid=" + nid;
	popup(myLink, 'View History', {
		width: 500,
		height: 250
	});
}

/* This function -
 * - Initialize a chat node
 * - checks if chat html exists, if exists opens that node otherwise creates html for new node
 * @param string chatuser e.g. admin__3 [ususally - windowtitle__nodeid]
 * @param string stcls e.g. statusicon_1 [ususally - statusicon_class]
 * */
function chatWith(chatuser, stcls) {
	cuser = chatuser.split('__'); /*manoj__3*/
	/*if chatbox html does not exists*/
	if (techjoomla.jQuery("#chatbox_" + cuser[1] + "_outer").length == 0) { /*chatbox_3_outer*/
		checkOpenChatBoxes();
		if (include(nodeid_count, cuser[1])) {
			nodeid_count.push(cuser[1]);
		} else {
			nodeid_count.push(cuser[1]);
		}
		techjoomla.jQuery.tmpl(document.getElementById("chatwindow"), {
			user: cuser[0],
			id: cuser[1],
			stcls: stcls,
			chatid: 'chatbox_' + cuser[1],
			chattype: cuser[1] + "_1",
			ctype: 1
		}).appendTo(".jbolochatwin");
		techjoomla.jQuery(".jbolochatwin").css('display', 'block');
		techjoomla.jQuery("#chatbox_" + cuser[1] + "textarea .chatboxtextarea").focus();
		setTimeout(function () { /*weird bug - to fix auto focus set 300 miliseconds timeout*/
			techjoomla.jQuery("#chatbox_" + cuser[1] + ' .remainingwindow > .inmessages > .chatboxtextarea').focus();
		}, 300);
	} else /*if chatbox html exists*/ {
		if (techjoomla.jQuery("#" + cuser[1] + '_chat').is(":hidden")) {
			checkOpenChatBoxes();
			techjoomla.jQuery("#chatbox_" + cuser[1] + "_outer").removeClass();
			techjoomla.jQuery("#chatbox_" + cuser[1] + " textarea").focus();
			techjoomla.jQuery("#chatbox_" + cuser[1] + "_outer").addClass('jbolo_container');
			techjoomla.jQuery("#" + cuser[1] + "_chat").css('display', 'block');
			techjoomla.jQuery("#chatbox_" + cuser[1] + "_outer").toggle();
		} else {
			/*techjoomla.jQuery("#chatbox_"+cuser[1] + "_outer").css('display','block');*/
		}
		techjoomla.jQuery("#chatbox_" + cuser[1] + " textarea").focus();
	}
	updateOpenCookie(user_id + "_" + cuser[1]);
}

function chat_window_function() {
	techjoomla.jQuery('.jbolo_close').live('click', function () {
		var id = techjoomla.jQuery(this).attr('id');
		closeChatBox(id);
	});
	techjoomla.jQuery('.jbolo_title').live('click', function () {
		var id = techjoomla.jQuery(this).attr('id');
		toggleChatBox(id);
	});
}

/* This function - pushes a message to a node on enter key press
 * @param array event - keypress event
 * @param string chatboxtextarea - textarea html
 **/
function checkChatBoxInputKey(event, chatboxtextarea) {
	var pid = (techjoomla.jQuery(chatboxtextarea).parent().attr('id'));
	var pp = pid.split('_');
	if (event.keyCode == 13 && event.shiftKey == 0) {
		var ts = Math.round((new Date()).getTime() / 1000);
		message = techjoomla.jQuery(chatboxtextarea).val();
		message = message.replace(/^\s+|\s+techjoomla.jQuery/g, "");
		techjoomla.jQuery(chatboxtextarea).focus();
		if (message != '') {
			techjoomla.jQuery.ajax({
				type: 'POST',
				url: site_link + 'index.php?option=com_jbolo&action=pushChatToNode',
				async: false,
				data: {
					nid: pp[1],
					msg: message
				},
				dataType: 'json',
				success: function (data) {
					var jboloError = 0;
					if (data.validate.error) {
						jboloError = data.validate.error;
					}
					if (jboloError === 1) {
						alert(data.validate.error_msg);
						return 0;
					}
					updateChatHistory(pp[1]);
					/*message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");*/
					message = data.pushChat_response.msg; /*changed for text processing*/
					techjoomla.jQuery.tmpl(document.getElementById("chatmessage"), {
						me: jbolo_lang['COM_JBOLO_ME'],
						message: message,
						avtr: "'" + me_avtr + "'"
					}).appendTo("#chatbox_" + pp[1] + " .chatmessages");
				}
			});
		}
		techjoomla.jQuery(chatboxtextarea).val('');
		var adjustedHeight = chatboxtextarea.clientHeight;
		var maxHeight = 94;
		setTimeout(function () { /*again strange bug so using timeout*/
			techjoomla.jQuery("#chatbox_" + pp[1] + " .chatmessages").scrollTop(techjoomla.jQuery("#chatbox_" + pp[1] + " .chatmessages").prop("scrollHeight") - techjoomla.jQuery("#chatbox_" + pp[1] + '.chatmessages').height());
		}, 300);
		jb_chatHeartbeatTime = jb_minChatHeartbeat;
		jb_chatHeartbeatCount = 1;
		return false;
	}
}

/* This function - checks if more than 4 chatwindows are open,  and if yes, calls closeExtraChatBoxes funcion
 **/
function checkOpenChatBoxes() {
	var showcount = 0;
	/*if (techjoomla.jQuery('div.jbolo_container >.jbolo_container').length >= 4)*/
	if (techjoomla.jQuery('div.jbolo_container .jbolo_container').length > 2) {
		nodeid_count = nodeid_count.getUnique();
		for (var i = 0; i < nodeid_count.length; i++) {
			/*check if the chatwindows on the page are visible i.e open*/
			if (techjoomla.jQuery("#chatbox_" + nodeid_count[i]).is(":visible")) {
				showcount++;
				/*showcount--;*/
			} else {
				/*showcount++;*/
			}
		}
		if (showcount > 3) /*if more than 4 chatwindows are open, call closeExtraChatBoxes funcion*/
		{
			closeExtraChatBoxes();
		}
	}
}

/* This function -  Clears chat window - Clears chats for give node from session window
 * @param integer nid e.g. 3
 **/
function clearChat(nid) {
	/*clear all chats for current nid from session*/
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=clearchat",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			nid: nid
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			/*clear all chats for current chatbox window*/
			techjoomla.jQuery('#chatbox_' + nid + '_chat').empty();
		}
	});
}

/* This function - Closes chatbox for given chatbox id
 * @param id integer e.g. 3 [usually nid]
 **/
function closeChatBox(id) {
	techjoomla.jQuery("#chatbox_" + id + "_outer").css('display', 'none'); /*chatbox_3_outer*/
	updateCloseCookie(user_id + "_" + id); /*776_3*/
	close_flag = 0;
	/*delmin( user_id+"_"+id );*/
}

/* This function - closes open chat windows to keep open chat windows in limit(max 4 open windows)
 **/
function closeExtraChatBoxes() {
	var countbox = 1;
	if (jbolo_chat_history.length < 4) {
		var nd = jbolo_open[0].split('_');
		techjoomla.jQuery("#chatbox_" + nd[1] + "_outer").css('display', 'none');
		updateCloseCookie(jbolo_open[0]);
	} else {
		for (var i = 0; i < jbolo_chat_history.length; i++) {
			if (countbox) {
				if (techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i] + "_outer").is(":hidden")) {
					if (techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i + 1] + "_outer").is(":hidden")) {
						techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i + 1] + "_outer").css('display', 'none');
						updateCloseCookie(user_id + "_" + jbolo_chat_history[i + 1]);
						jbolo_chat_history.splice(jbolo_chat_history.indexOf(jbolo_chat_history[i]), 1);
						countbox = 0;
					}
					techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i + 1] + "_outer").css('display', 'none');
					updateCloseCookie(user_id + "_" + jbolo_chat_history[i + 1]);
					jbolo_chat_history.splice(jbolo_chat_history.indexOf(jbolo_chat_history[i]), 1);
					countbox = 0;
				} else {
					techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i] + "_outer").css('display', 'none');
					updateCloseCookie(user_id + "_" + jbolo_chat_history[i]);
					jbolo_chat_history.splice(jbolo_chat_history.indexOf(jbolo_chat_history[i]), 1);
					countbox = 0;
				}
			}
		}
	}
}

/* This - change color of offline user window and notify that user go offline
 * @param string ctid e.g-user__1(offline user id)
 * @param int uid e.g-1(loged in user id)
 */
function close_offlinebox(ctid, uid) {
	var str = ctid.split('__');
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=getUserNodes",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			onuser: uid,
			offuser: str[1]
		},
		success: function (data) {
			techjoomla.jQuery("#chatbox_" + data.nodes + ' .jbolo_title').css('background', 'grey');
			techjoomla.jQuery("#chatbox_" + data.nodes + ' #statusicon').removeClass();
			techjoomla.jQuery("#chatbox_" + data.nodes + ' #statusicon').addClass('statusicon_4');
			techjoomla.jQuery("#chatbox_" + data.nodes + ' #offline').removeClass();
			techjoomla.jQuery("#chatbox_" + data.nodes + ' #offline').addClass('offline-msg1');
		}
	});
}

/* This-Remove old chat window from display when reached max length.
 **/
function closemaxchatold() {
	var countbox = 1;
	for (var i = 0; i < jbolo_chat_history.length; i++) {
		if (countbox) {
			if (techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i]).is(":hidden")) {} else {
				techjoomla.jQuery("#chatbox_" + jbolo_chat_history[i]).css('display', 'none');
				updateCloseCookie(user_id + "_" + jbolo_chat_history[i]);
				countbox = 0;
			}
		}
	}
}

/* This function - Create chat window
 * @param integer chatboxtitle e.g. 1
 * @param string username e.g. admin and window title in group chat
 * @param string msg e.g. hello
 * @param string avtr e.g. http://blah-blah/com_jbolo/jbolo/view/facebook/images/avtr_default.png
 * @param integer box_state e.g. 2
 * @param string msg_user e.g. Me
 * @param string ctype e.g. 1
 * @param string from e.g. poll
 **/
function createChatWindow(chatboxtitle, username, msg, avtr, box_state, msg_user, ctype, from, pro_url, ts) {
	var bxst;
	if (box_state == 1) {
		bxst = "display:none";
	} else {
		bxst = "display:none"; /*@TODO really?*/
	}
	if (techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer").length == 0) {
		techjoomla.jQuery.tmpl(document.getElementById("chatwindow"), {
			user: username,
			id: chatboxtitle,
			stcls: 'statusicon',
			bxst: bxst,
			chatid: 'chatbox_' + chatboxtitle,
			chattype: chatboxtitle + "_1",
			ctype: ctype
		}).appendTo(".jbolochatwin");
		techjoomla.jQuery("#chatbox_" + chatboxtitle + " .chatboxtextarea").focus();
		if (from == "poll") {
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer #chatbox_" + chatboxtitle + ' .jbolo_title').addClass('titleHighlight');
		}
		if (box_state == 1) {
			techjoomla.jQuery("#" + chatboxtitle + '_chat').css('display', 'none');
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer").removeClass();
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer").addClass('jbolo_container minimise');
		} else {
			/*techjoomla.jQuery("#chatbox_"+chatboxtitle + "_outer").css('display','display');*/
		}
		techjoomla.jQuery(".jbolochatwin").show();
	}
	pushMsgToChatWindow(chatboxtitle, msg_user, msg, avtr, box_state, from, pro_url, ts);
}

/* This - remove nid from close cookie
 * @param integer el e.g. 3 [usually nid]
 **/
function delclose(el) {
	var indx = el;
	if (el) {
		var options = new Array();
		options['path'] = '/';
		jbolo_close.splice(jbolo_close.indexOf(indx), 1);
		gsCookie("jbolo_close", jbolo_close.join('|'), options);
	}
}

/* This - remove nid from minimize cookie
 * @param integer el e.g. 3 [usually nid]
 **/
function delmin(el) {
	var indx = el;
	if (el) {
		var options = new Array();
		options['path'] = '/';
		jbolo_mini.splice(jbolo_mini.indexOf(indx), 1);
		gsCookie("jbolo_mini", jbolo_mini.join('|'), options);
	}
}/*not used anywhere*/

/* This function - Add user to node
 * @param int nid e.g-1
 * @param int uid e.g-11
 * @param int ctype e.g-2(1-one2one chat,2-group chat)
 */
function init_groupchat(nid, pid, ctype) {
	/* set heartbeat time to jb_minChatHeartbeat second, and poll, as we want it to poll quick after adding new user**/
	jb_chatHeartbeatTime = jb_minChatHeartbeat;
	poll_msg();
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=addNodeUser",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			nid: nid,
			pid: pid
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			nid = data.nodeinfo.nid;
			rec_name = data.nodeinfo.wt;
			var n = rec_name + "__" + nid;
			open_gc(n);
		}
	});
}

/* This function - Initialize a chat node
 * @param integer uid e.g. 776 who initiates chat
 * @param integer pid e.g. 778 participant
 * @param string stcls e.g. statusicon_1 CSS class for status
 * @return integer nid e.g. 4 node id
 */
function init_node(uid, pid, stcls) {
	/*794,793,statusicon_1*/
	/*@TODO uid not used in below code*/
	var nid;
	techjoomla.jQuery.ajax({
		type: 'POST',
		url: site_link + "index.php?option=com_jbolo&action=initiateNode",
		async: false,
		data: {
			pid: pid
		},
		dataType: 'json',
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			nid = data.nodeinfo.nid;
			var n = data.nodeinfo.wt + "__" + nid; /*manoj__3*/
			chatWith(n, stcls); /*manoj__3,statusicon_1*/
		}
	});
	return nid; /*added for jbolo-hd integration*/
}

/* This function - Lets user leave group chat window
 * @param integer nid e.g. 3 [usally - node id]
 */
function leaveChat(nid) { /*confirm leaving chat*/
	if (!confirm(jbolo_lang.COM_JBOLO_LEAVE_CHAT_CONFIRM_MSG)) {
		return;
	}
	/* set heartbeat time to jb_minChatHeartbeat second, and poll, as we want it to poll quick after leaving chat */
	jb_chatHeartbeatTime = jb_minChatHeartbeat;
	poll_msg();
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=leavechat",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			nid: nid
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			alert(data.lcresponse.msg);
		}
	});
}

/* called on jQuery(document).ready
 **/
function list_opener() {
	techjoomla.jQuery("#jbolouserlist_container").show();
	techjoomla.jQuery("#jbolouserlist").show();
	/*this is to set cookie for opening userlist*/
	if (gsCookie("open_list") == 0) {
		techjoomla.jQuery("#jbolouserlist_container").toggle();
		techjoomla.jQuery('#listopener').removeClass();
		techjoomla.jQuery('#listopener').addClass('listopener highlight');
	}
	techjoomla.jQuery("#listopener").click(function () {
		techjoomla.jQuery("#jbolouserlist_container").toggle();
		/*this is to set cookie for opening userlist*/
		var options = new Array();
		options['path'] = '/';
		if (techjoomla.jQuery("#jbolouserlist_container").is(":hidden")) {
			gsCookie("open_list", 0, options);
		} else {
			gsCookie("open_list", 1, options);
		}
		if (techjoomla.jQuery("#jbolouserlist_container").is(":hidden")) {
			techjoomla.jQuery('#listopener').removeClass();
			techjoomla.jQuery('#listopener').addClass('listopener highlight');
		} else {
			techjoomla.jQuery('#listopener').removeClass();
			techjoomla.jQuery('#listopener').addClass('listopener');
		}
		techjoomla.jQuery("#onlineusers").text("Chat  (" + techjoomla.jQuery(".unamecover").length + ")");
	});
}

/* This - returns box state e.g. 1
 * @param integer c e.g. 3 [usually nid]
 * @return integer boxst e.g -1
 */
function open_chat(c) {
	var boxst = 2;
	techjoomla.jQuery.each(jbolo_mini, function () {
		var id = this; /*parseInt(this,10);*/
		var pid = id.split('_');
		if (pid[0] == user_id && pid[1] == c) {
			boxst = 1;
		}
	});
	techjoomla.jQuery.each(jbolo_close, function () {
		var id = this; /*parseInt(this,10);*/
		var pid = id.split('_');
		if (pid[0] == user_id && pid[1] == c) {
			boxst = 3;
		}
	});
	return boxst;
}

/* This function - open group chat window
 * @param string chatuser e.g-vishnu_1(vishnu-participant name,1-nid)
 **/
function open_gc(chatuser) {
	cuser = chatuser.split('__');
	if (techjoomla.jQuery("#chatbox_" + cuser[1]).length == 0) {
		checkOpenChatBoxes();
		nodeid_count.push(cuser[1]);
		updateOpenCookie(user_id + "_" + cuser[1]);
		techjoomla.jQuery.tmpl(document.getElementById("chatwindow"), {
			user: cuser[0],
			id: cuser[1],
			stcls: 'statusicon',
			chatid: 'chatbox_' + cuser[1],
			chattype: cuser[1] + "_2",
			ctype: 2
		}).appendTo(".jbolochatwin");
		techjoomla.jQuery("#chatbox_" + cuser[1]).css('display', 'block');
		/*updateOpenCookie( user_id+"_"+cuser[1] );*/
		techjoomla.jQuery(".jbolochatwin").css('display', 'block');
		techjoomla.jQuery("#chatbox_" + cuser[1] + "textarea .chatboxtextarea").focus();
		techjoomla.jQuery("#chatbox_" + cuser[1]).find('#wt').text(cuser[0]);
	} else {
		if (techjoomla.jQuery("#" + cuser[1] + '_chat').is(":hidden")) {
			updateOpenCookie(user_id + "_" + cuser[1]);
			techjoomla.jQuery("#chatbox_" + cuser[1]).removeClass();
			techjoomla.jQuery("#chatbox_" + cuser[1] + " textarea").focus();
			techjoomla.jQuery("#chatbox_" + cuser[1]).addClass('jbolo_container');
			techjoomla.jQuery("#" + cuser[1] + '_chat').css('display', 'block');
		}
		techjoomla.jQuery("#chatbox_" + cuser[1]).css('display', 'block');
		techjoomla.jQuery("#chatbox_" + cuser[1] + " textarea").focus();
		techjoomla.jQuery("#chatbox_" + cuser[1]).find('#wt').text(cuser[0]);
	}
}

/* called on jQuery(document).ready
 **/
function outerlist_fun() {
	techjoomla.jQuery.tmpl(document.getElementById("outerlist")).appendTo(".jbolouserlist");
	techjoomla.jQuery("#updatedstatus").hide();/*hide div used to enter custom status msg*/
	techjoomla.jQuery("#userstatus").click(function () {
		techjoomla.jQuery("#extstatuslist").hide(); /*hide sts dropdwon list div*/
		techjoomla.jQuery('#userstatus').val(techjoomla.jQuery('#textar').val());
		techjoomla.jQuery("#updatedstatus").toggle();
		techjoomla.jQuery('#textar').focus();
		techjoomla.jQuery('#updatedstatus').keydown(function (event) {
			if (event.which == 13) {
				event.preventDefault();
				techjoomla.jQuery('#userstatus').val(techjoomla.jQuery('#textar').val());
				changeStatusMsg(techjoomla.jQuery('#userstatus').val(), user_id);
				techjoomla.jQuery("#updatedstatus").hide();
				var value = techjoomla.jQuery('#textar').val();
				if (value && value.length > 0) {
					techjoomla.jQuery('#userstatus').text(techjoomla.jQuery('#textar').val());
				} else {
					/*@TODO user might want to make status as blank*/
					techjoomla.jQuery('#userstatus').text(jbolo_lang['COM_JBOLO_SET_STATUS']);
				}
			}
		});
	});
	/*for change status icon and status message of logged user*/
	techjoomla.jQuery("#extstatuslist").hide();
	techjoomla.jQuery("#extstatus").click(function () {
		techjoomla.jQuery('#updatedstatus').hide();
		techjoomla.jQuery("#extstatuslist").toggle();
	});
	techjoomla.jQuery('.uz').live('click', function () {
		if (techjoomla.jQuery(this).attr('id') == 5) {
			techjoomla.jQuery('#userstatus').text(jbolo_lang['COM_JBOLO_SET_STATUS']);
			var blank = "";
			changeStatusMsg(blank, user_id);
			techjoomla.jQuery("#extstatuslist").hide();
		} else {
			changeStatus(techjoomla.jQuery(this).attr('id'), techjoomla.jQuery('#userstatus').text(), user_id);
		}
	});
	/*for autocomplete search users function*/
	techjoomla.jQuery("#jbolo_search").focus(function () {
		this.value = '';
		techjoomla.jQuery("#jbolo_search").quicksearch('div .useridd_hover', {
			selector: 'span:first-child',
			delay: 100,
			noResults: '#noresults',
			stripeRows: ['odd', 'even'],
		});
	});
} /*End of outerlist template*/

/* This play audio
 * - Add audio path to tag.
 **/
var currentFile = "";
function playAudio() {
	var oAudio = document.getElementById('myaudio');
	/*See if we already loaded this audio file.*/
	if (techjoomla.jQuery("#audiofile").val() !== currentFile) {
		oAudio.src = techjoomla.jQuery("#audiofile").val();
		currentFile = techjoomla.jQuery("#audiofile").val();
	}
	var test = techjoomla.jQuery("#myaudio");
	test.src = techjoomla.jQuery("#audiofile").val();
	oAudio.play();
}
techjoomla.jQuery(function () {
	if (Modernizr.audio) {
		if (Modernizr.audio.wav) {
			techjoomla.jQuery("#audiofile").val(site_link + "components/com_jbolo/jbolo/assets/sounds/sample.wav");
		}
		if (Modernizr.audio.mp3) {
			techjoomla.jQuery("#audiofile").val(site_link + "components/com_jbolo/jbolo/assets/sounds/sample.mp3");
		}
	} else {
		techjoomla.jQuery("#HTML5Audio").hide();
		techjoomla.jQuery("#OldSound").html("<embed autostart=false width=1 height=1 id='LegacySound' enablejavascript='true'src=" + site_link + "/components/com_jbolo/jbolo/assets/sounds/sample.wav >");
	}
});

/* This -Poll userlist,new message,status change
 **/
function poll_msg() {
	var itemsfound = 0;
	techjoomla.jQuery('.nouser').remove();
	var ts = Math.round((new Date()).getTime() / 1000);
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=polling",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {
			logtim: wasted_minutes
		},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			techjoomla.jQuery.each(data.nodes, function (i, nodes) {
				if (nodes) {
					chatboxtitle = nodes.nodeinfo.nid;
					techjoomla.jQuery("#chatbox_" + chatboxtitle).find('#wt').text(nodes.nodeinfo.wt); /*msguser=nodes.messages[i].fid;*/
					if (techjoomla.jQuery("#chatbox_" + chatboxtitle).length == 0) {
						nodeid_count.push(chatboxtitle);
						var username;
						var avtr;
						var msg_user;
						var purl;
						if (nodes.messages != undefined) {
							for (var j = 0; j < nodes.messages.length; j++) {
								if (nodes.messages[j].fid == 0) /*this is a broadcast message*/ {
									/*username='';*/
									username = nodes.nodeinfo.wt;
									msg_user = '';
									avtr = '';
									purl = "";
								} else {
									username = nodes.nodeinfo.wt;
									msg_user = nodes.participants[nodes.messages[j].fid].uname;
									avtr = nodes.participants[nodes.messages[j].fid].avtr;
									purl = nodes.participants[nodes.messages[j].fid].purl;
								}
								var msg = nodes.messages[j].msg;
								var tstamp = nodes.messages[j].ts;
								checkOpenChatBoxes();
								createChatWindow(chatboxtitle, username, msg, avtr, 2, msg_user, nodes.nodeinfo.ctyp, "poll", purl, tstamp);
								if (jb_windowFocus == false) {
									document.title = username + jbolo_lang['COM_JBOLO_SAYS'];
									playAudio();
								}
								if (nodes.nodeinfo.ctyp == 2) {
									techjoomla.jQuery("#chatbox_" + chatboxtitle + " .plist").empty();
									techjoomla.jQuery.each(nodes.participants, function (i, participant) {
										techjoomla.jQuery.tmpl(document.getElementById("pdetails"), {
											username: participant.uname,
											userid: participant.uid,
											stcls: "statusicon_" + participant.sts,
											avtr: "'" + participant.avtr + "'"
										}).appendTo("#chatbox_" + chatboxtitle + " .plist");
									});
								}
								updateChatHistory(chatboxtitle);
								itemsfound += 1;
							} /*end for*/
						} /*end if*/
					} /*end if*/
					else {
						if (nodes.messages != undefined) {
							for (var j = 0; j < nodes.messages.length; j++) {
								if (nodes.messages[j].fid == 0) /*this is a broadcast message*/ {
									username = '';
									msg_user = '';
									avtr = '';
									purl = '';
								} else {
									username = nodes.nodeinfo.wt;
									msg_user = nodes.participants[nodes.messages[j].fid].uname;
									avtr = nodes.participants[nodes.messages[j].fid].avtr;
									purl = nodes.participants[nodes.messages[j].fid].purl;
								}
								msg = nodes.messages[j].msg;
								ts = nodes.messages[j].ts; /*createChatWindow(chatboxtitle,username,msg,avtr,2,msg_user);*/
								/*checkOpenChatBoxes();*/
								box = open_chat(chatboxtitle);
								pushMsgToChatWindow(chatboxtitle, msg_user, msg, avtr, box, "poll", purl, ts);
								if (jb_windowFocus == false) {
									document.title = username + jbolo_lang['COM_JBOLO_SAYS'];
									playAudio();
								}
								if (nodes.nodeinfo.ctyp == 2) {
									techjoomla.jQuery("#chatbox_" + chatboxtitle + " .plist").empty();
									techjoomla.jQuery.each(nodes.participants, function (i, participant) {
										if (participant.active == 1) {
											techjoomla.jQuery.tmpl(document.getElementById("pdetails"), {
												username: participant.uname,
												userid: participant.uid,
												stcls: "statusicon_" + participant.sts,
												avtr: "'" + participant.avtr + "'"
											}).appendTo("#chatbox_" + chatboxtitle + " .plist");
										}
									});
								}
								updateChatHistory(chatboxtitle);
								itemsfound += 1;
							} /*end for*/
						} /*end if*/
					} /*end else*/
				} /*end if nodes*/
			}); /*end techjoomla.jQuery.each*/
			jb_chatHeartbeatCount++;
			if (itemsfound > 0) {
				jb_chatHeartbeatTime = jb_minChatHeartbeat;
				jb_chatHeartbeatCount = 1;
			} else if (jb_chatHeartbeatCount >= 4) {
				jb_chatHeartbeatTime *= 2;
				jb_chatHeartbeatCount = 1;
				if (jb_chatHeartbeatTime > jb_maxChatHeartbeat) {
					jb_chatHeartbeatTime = jb_maxChatHeartbeat;
				}
			}
			if (data == null || data.logout == undefined || data.logout != 1) setTimeout('poll_msg();', jb_chatHeartbeatTime);
			poll_list = [];
			techjoomla.jQuery("#onlineusers").text(jbolo_lang['COM_JBOLO_CHAT'] + "(" + data.userlist.users.length + ")");
			me_avtr = data.userlist.me.avtr;
			if (data.userlist.users.length == 0) {
				techjoomla.jQuery('<div class="nouser">' + jbolo_lang['COM_JBOLO_NO_USERS_ONLINE'] + '</div>').appendTo('.ulist');
			}
			/* data.nsts gives pair of nid and status*/
			if (data.nsts) {
				for (var key in data.nsts) {
					if (data.nsts[key] > -1) {
						update_boxstatus(key, data.nsts[key]);
					}
				}
			}
			techjoomla.jQuery.each(data.userlist.users, function (i, users) { /*userdetail=[];*/
				var m = 'user__' + data.userlist.users[i].uid;
				poll_list.push('user__' + data.userlist.users[i].uid);
				/*update_boxstatus(data.userlist.users[i].uid,user_id,data.userlist.users[i].sts);*/
				techjoomla.jQuery('#sti_' + data.userlist.users[i].uid).removeClass();
				techjoomla.jQuery('#sti_' + data.userlist.users[i].uid).addClass('statusicon_' + data.userlist.users[i].sts);
				techjoomla.jQuery('#' + data.userlist.users[i].uid).text(data.userlist.users[i].stsm);
				if (!techjoomla.jQuery("#" + m).length) {
					techjoomla.jQuery('.nouser').remove();
					techjoomla.jQuery.tmpl(document.getElementById("listtemplate"), users).appendTo(".ulist");
					techjoomla.jQuery('#sts').attr('id', data.userlist.users[i].uid);
					techjoomla.jQuery('#sti').attr('id', 'sti_' + data.userlist.users[i].uid);
					techjoomla.jQuery('#singleListUserDetails').attr('id', m);
				} /*end if*/
			}); /*end of each*/
			for (var k = 0; k < old_list.length; k++) {
				if (techjoomla.jQuery.inArray(old_list[k], poll_list) >= 0) {
					/*keep.push(real_data[k]);*/
					} else {
					techjoomla.jQuery('#' + old_list[k]).remove();
					close_offlinebox(old_list[k], user_id);
				}
			}
			old_list = [];
			old_list = poll_list;
		}
	});
}

/* This-Open popup
 * @param string mylink e.g-popup url
 * @param string mylink e.g-'Send File'
 * @param obj size  e.g-{width:500,height:250}
 * */
function popup(mylink, windowname, size) {
	var windowsize = size || {
		width: 400,
		height: 400
	};
	if (!window.focus) return true;
	var href;
	if (typeof (mylink) == 'string') href = mylink;
	else href = mylink.href;
	if (techjoomla.jQuery.browser.msie) windowname = '';
	window.open(href, windowname, 'width=' + windowsize.width + ',height=' + windowsize.height + ',scrollbars=yes');
	return false;
}

/* This function - Push msg to chat window
 *
 * @param integer chatboxtitle e.g. 1
 * @param string username e.g. admin
 * @param string msg e.g. hello
 * @param string avtr e.g. http://blah-blah/components/com_jbolo/jbolo/view/facebook/images/avtr_default.png
 * @param integer box_state e.g. 1
 * @param string fro e.g. "st"
 * @param string pro_url e.g. http://blah-blah/index.php/cb-profile/userprofile/admin
 */
function pushMsgToChatWindow(chatboxtitle, username, msg, avtr, box_state, fro, pro_url, ts) {
	techjoomla.jQuery("#chatbox_" + chatboxtitle + " textarea").focus();
	/*@TODO BUG -
	 * if we use predfined template for chat window line below shows every msgs as new msg when page refreshed
	 * */
	if (fro != "st") {
		/*msg is from polling*/
		techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer #chatbox_" + chatboxtitle + ' .jbolo_title').addClass('titleHighlight');
	}
	if (box_state == 3) {
		techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer").css('display', 'none');
		if (fro == "poll") {
			checkOpenChatBoxes();
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer").toggle();
			updateOpenCookie(user_id + '_' + chatboxtitle);
		}
	} else {
		/*techjoomla.jQuery("#chatbox_"+chatboxtitle + "_outer").css('display','block');*/
	}
	/*using timeout 1000 [1second] for - proper ordering of chat messages*/
	setTimeout(function () {
		techjoomla.jQuery.tmpl(document.getElementById("chatmessage"), { /*using jquery template now*/
			me: username,
			message: msg,
			purl: "'" + pro_url + "'",
			avtr: "'" + avtr + "'",
			ts: ts
		}).appendTo("#chatbox_" + chatboxtitle + " .chatmessages");
		techjoomla.jQuery("#chatbox_" + chatboxtitle + " .inmessages").focus();
		techjoomla.jQuery("#chatbox_" + chatboxtitle).live('click', function () {
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer #chatbox_" + chatboxtitle + ' .jbolo_title').addClass('titleOriginal');
			techjoomla.jQuery("#chatbox_" + chatboxtitle + "_outer #chatbox_" + chatboxtitle + ' .jbolo_title').removeClass('titleHighlight');
		});
		techjoomla.jQuery("#chatbox_" + chatboxtitle + " .chatmessages").animate({
			scrollTop: techjoomla.jQuery("#chatbox_" + chatboxtitle + " .chatmessages").prop("scrollHeight") - techjoomla.jQuery("#chatbox_" + chatboxtitle + ' .chatmessages').height()
		}, 0);
	}, 1000);
}

/* This - Sending file
 * @params nid e.g-3
 */
function sendFile(nid) {
	myLink = site_link + "index.php?option=com_jbolo&tmpl=component&view=sendfile&nid=" + nid;
	popup(myLink, 'Send File', {
		width: 800,
		height: 500
	});
}

/* This - shows smiley box when clicked on smiely icon in chatbox window
 * @param htmlElement selector
 **/
function showSmiley(selector) {
	if (techjoomla.jQuery(selector).parent().find(".jb_smileybox").css("display") == 'block') {
		techjoomla.jQuery(selector).parent().find(".jb_smileybox").css("display", "none");
		return false;
	}
	if (smilehtml != null) {
		techjoomla.jQuery(selector).parent().html(smilehtml);
		return;
	}
	techjoomla.jQuery.ajax({
		url: site_link + "components/com_jbolo/jbolo/assets/smileys.txt",
		success: function (data) {
			smilebackhtml = data;
			var smileyarr = data.split("\n");
			smilehtml = '<button onclick="javascript:showSmiley(this);" alt="" class="smiley" id="smiley"></button><div class=jb_smileybox><table><tr>';
			var getsmiledata = new Array();
			for (var i = 0; i < smileyarr.length - 1; i++) {
				var getdata = smileyarr[i].split("=");
				getsmiledata.push(getdata[1]);
			}
			getsmiledata = jbunique(getsmiledata);
			for (var i = 0; i < getsmiledata.length; i++) {
				if (i % 2 == 0 && i != 0) {
					smilehtml += '</tr><tr>';
				}
				smilehtml += '<td><img src="' + site_link + 'components/com_jbolo/jbolo/view/' + template + '/images/smileys/default/' + getsmiledata[i] + '"  onClick="javascript:smileyClicked(this);" class="smiley"/></td>';
			}
			smilehtml += '</tr></table></div>';
			techjoomla.jQuery(selector).parent().html(smilehtml);
		}
	});
}

/* This - hides smileybox when clicked on a smiley - pushes smiley code in textinput area
 * @param htmlElement selector
 **/
function smileyClicked(selector) {
	techjoomla.jQuery(selector).parent().parent().parent().parent().parent().hide();
	var srcarr = techjoomla.jQuery(selector).attr("src").split("/");
	if (smilebackhtml != null) {
		var smileyarr = smilebackhtml.split("\n");
		for (var i = 0; i < smileyarr.length; i++) {
			var getdata = smileyarr[i].split("=");
			if (getdata[1] == srcarr[srcarr.length - 1]) {
				techjoomla.jQuery(selector).parent().parent().parent().parent().parent().parent().parent().parent().find(".chatboxtextarea").insertAtCaret(getdata[0]);
				techjoomla.jQuery(selector).parent().parent().parent().parent().parent().parent().parent().parent().find(".chatboxtextarea").focus();
				break;
			}
		}
		return;
	}
}

/* This - restore session
 */
function start_chat_session() {
	/*jb_originalTitle = document.title;*/
	nodeid_count = [];
	techjoomla.jQuery.ajax({
		url: site_link + "index.php?option=com_jbolo&action=startChatSession",
		cache: false,
		type: 'POST',
		dataType: "json",
		data: {},
		success: function (data) {
			var jboloError = 0;
			if (data.validate.error) {
				jboloError = data.validate.error;
			}
			if (jboloError === 1) {
				alert(data.validate.error_msg);
				return 0;
			}
			techjoomla.jQuery("#onlineusers").text(jbolo_lang['COM_JBOLO_CHAT'] + "(" + data.userlist.users.length + ")"); /*Chat(4)*/
			if (data.userlist.users.length == 0) {
				techjoomla.jQuery('<div class="nouser">' + jbolo_lang['COM_JBOLO_NO_USERS_ONLINE'] + '</div>').appendTo('.ulist');
			}
			techjoomla.jQuery.each(data.userlist.users, function (i, users) {
				var m = 'user__' + data.userlist.users[i].uid;
				old_list.push('user__' + data.userlist.users[i].uid);
				techjoomla.jQuery.tmpl(document.getElementById("listtemplate"), users).appendTo(".ulist");
				techjoomla.jQuery('#sts').attr('id', data.userlist.users[i].uid);
				techjoomla.jQuery('#sti').attr('id', 'sti_' + data.userlist.users[i].uid);
				techjoomla.jQuery('#singleListUserDetails').attr('id', 'user__' + data.userlist.users[i].uid);
				techjoomla.jQuery('#sti_' + data.userlist.users[i].uid).removeClass();
				techjoomla.jQuery('#sti_' + data.userlist.users[i].uid).addClass('statusicon_' + data.userlist.users[i].sts);
				techjoomla.jQuery('#' + data.userlist.users[i].uid).text(data.userlist.users[i].stsm);
			}); /*end techjoomla.jQuery.each*/
			var n = data.userlist.me;
			me_avtr = data.userlist.me.avtr;
			log_username = data.userlist.me.uname;
			techjoomla.jQuery.tmpl(document.getElementById("logged_user"), n).appendTo("#loggeduser");
			techjoomla.jQuery('#userstatus').text(data.userlist.me.stsm);
			var value = data.userlist.me.stsm;
			if (value && value.length > 0) {
				techjoomla.jQuery('#userstatus').text(value);
			} else {
				techjoomla.jQuery('#userstatus').text(jbolo_lang['COM_JBOLO_SET_STATUS']);
			}
			techjoomla.jQuery('.useridd_hover').live('click', function () {
				var id = techjoomla.jQuery(this).attr('id'); /*user__793*/
				var str = id.split('__'); /*get status class from id for this user*/
				/*<div class="statusicon_1" id="sti_793"></div>*/
				var stcls = techjoomla.jQuery('#sti_' + str[1]).attr('class'); /*uid, pid, stcls e.g.: 794,793,statusicon_1*/
				init_node(user_id, str[1], stcls);
			});
			techjoomla.jQuery.each(data.nodes, function (i, nodes) {
				if (nodes) {
					chatboxtitle = nodes.nodeinfo.nid;
					checkOpenChatBoxes(); /*added by manoj here - to limit max 4 windows afer referesh when no cookies*/
					if (techjoomla.jQuery("#chatbox_" + chatboxtitle).length == 0) {
						nodeid_count.push(chatboxtitle);
						var username;
						var avtr;
						var msg_user;
						var purl;
						if (nodes.messages != undefined) {
							var xzx = 1;
							for (var j = 0; j < nodes.messages.length; j++) {
								if (user_id == nodes.messages[j].fid) {
									username = nodes.nodeinfo.wt;
									msg_user = jbolo_lang['COM_JBOLO_ME'];
									avtr = nodes.participants[nodes.messages[j].fid].avtr;
									purl = nodes.participants[nodes.messages[j].fid].purl;
								} else {
									username = nodes.nodeinfo.wt;
									if (nodes.messages[j].fid == 0) {
										/*this is a broadcast message*/
										msg_user = '';
										avtr = '';
									} else {
										msg_user = nodes.participants[nodes.messages[j].fid].uname;
										avtr = nodes.participants[nodes.messages[j].fid].avtr;
										purl = nodes.participants[nodes.messages[j].fid].purl;
									}
								}
								var msg = nodes.messages[j].msg;
								var tstamp = nodes.messages[j].ts;
								var box = open_chat(chatboxtitle);
								if (xzx) {
									createChatWindow(chatboxtitle, username, msg, avtr, box, msg_user, nodes.nodeinfo.ctyp, "st", purl, tstamp);
									/*Show all users of group chat*/
									if (nodes.nodeinfo.ctyp == 2) {
										techjoomla.jQuery("#chatbox_" + chatboxtitle + " .plist").empty();
										techjoomla.jQuery.each(nodes.participants, function (i, participant) {
											if (participant.active == 1) {
												techjoomla.jQuery.tmpl(document.getElementById("pdetails"), {
													username: participant.uname,
													userid: participant.uid,
													stcls: "statusicon_" + participant.sts,
													avtr: "'" + participant.avtr + "'"
												}).appendTo("#chatbox_" + chatboxtitle + " .plist");
											}
										});
									}
									updateChatHistory(chatboxtitle);
									xzx = 0;
								} else {
									pushMsgToChatWindow(chatboxtitle, msg_user, msg, avtr, box, "st", purl, tstamp);
									updateChatHistory(chatboxtitle);
								}
							} /*end for*/
						} /*end if*/
					} /*end if*/
					else {
						if (nodes.messages != undefined) {
							for (var j = 0; j < nodes.messages.length; j++) {
								if (user_id == nodes.messages[j].fid) {
									username = jbolo_lang['COM_JBOLO_NO_USERS_ONLINE'];
								} else {
									username = nodes.participants[nodes.nodeinfo.tid].uname;
									purl = nodes.participants[nodes.nodeinfo.tid].purl;
									msg_user = nodes.participants[nodes.messages[j].fid].uname;
								}
								msg = nodes.messages[j].msg;
								tstamp = nodes.messages[j].ts;
								box = open_chat(chatboxtitle);
								pushMsgToChatWindow(chatboxtitle, msg_user, msg, avtr, box, "st", purl, tstamp);
								updateChatHistory(chatboxtitle);
							}
						}
					} /*end else*/
					if (jbolo_open.length > 5) {
						closemaxchatold();
					}
				} /*end if nodes*/
			}); /*end techjoomla.jQuery.each*/
			if (data.nsts) {
				for (var key in data.nsts) {
					if (data.nsts[key] > -1) {
						/*Update box status based on nid and status code.*/
						update_boxstatus(key, data.nsts[key]);
					}
				}
			}
			if (show_activity == 1) {
				if (template == 'facebook') /*load activity sream only for FB template*/ { /*push activity html*/
					techjoomla.jQuery.tmpl(document.getElementById("activitystream"), {
						ashtml: data.ashtml
					}).appendTo(".jboloactivity");
				}
			}
		} /*end sucess */
	});
} /*end function */

function timer_handler() {
	clearInterval(interval_pointer);
	wasted_minutes++;
	interval_pointer = setInterval(timer_handler, interval_period);
}

/* This function - toggles chatbox display (minimize/maximize) for given chatbox id
 * @param id integer e.g. 3 [usually nid]
 **/
function toggleChatBox(id) {
	techjoomla.jQuery("#chatbox_" + id + "_outer #chatbox_" + id + ' .jbolo_title').addClass('titleOriginal'); /*updateOpenCookie( user_id+"_"+id );*//*@TODO this causes close cookie to reset*/
	/*Check if div with id like 3_chat is hidden*/
	if (techjoomla.jQuery("#" + id + '_chat').is(":hidden")) {
		/*maximize window*/
		techjoomla.jQuery("#chatbox_" + id + "_outer").removeClass();
		techjoomla.jQuery("#chatbox_" + id + "_outer").addClass('jbolo_container');
		techjoomla.jQuery("#" + id + '_chat').css('display', 'block');
		/*delmin( user_id+"_"+id );*/
		if (close_flag) {
			updateOpenCookie(user_id + "_" + id);
		}
		/*var minimizedChatBoxes = new Array();*/
		/*gsCookie('chatbox_minimized'+id,id);*/
	} else { /*minimize window*/
		techjoomla.jQuery("#chatbox_" + id + "_outer").removeClass();
		techjoomla.jQuery("#chatbox_" + id + "_outer").addClass('jbolo_container minimise');
		techjoomla.jQuery("#" + id + '_chat').css('display', 'none');
		/*techjoomla.jQuery('#chatbox_' + chatboxtitle + ' .chatboxinput').css('display', 'none');*/
		updateMiniCookie(user_id + "_" + id);
		/*delclose(user_id+"_"+id );*/
	}
}

function updateChatHistory(el) {
	var indx = el;
	var tmp = jbolo_chat_history;
	var options = new Array();
	options['path'] = '/';
	if (el) {
		if (include(jbolo_chat_history, indx)) {
			jbolo_chat_history.splice(jbolo_chat_history.indexOf(indx), 1);
			tmp.push(indx);
			gsCookie("jbolo_chat_history", jbolo_chat_history.join('|'), options);
		} else {
			tmp.push(indx);
			gsCookie("jbolo_chat_history", jbolo_chat_history.join('|'), options);
		}
	}
	/*jbolo_chat_history= tmp.getUnique();*/
	/*gsCookie("jbolo_chat_history",jbolo_chat_history.join('|'),options);*/
}

/* This function -
 * - Adds current chatboxid to jbolo_close cookie,
 * - if present remove current chatboxid from jbolo_mini cookie,
 * - if present remove current chatboxid from jbolo_open cookie
 * @param el string e.g. 776_3 [usually uid_nodeid]
 **/
function updateCloseCookie(el) {
	var indx = el;
	var tmp = jbolo_close.getUnique(); /*["776_1", "776_2"]*/
	var options = new Array();
	options['path'] = '/';
	if (el) {
		tmp.push(indx);
		if (include(jbolo_mini, indx)) {
			/* if present remove current chatboxid from jbolo_mini */
			jbolo_mini.splice(jbolo_mini.indexOf(indx), 1);
			gsCookie("jbolo_mini", jbolo_mini.join('|'), options);
		}
		if (include(jbolo_open, indx)) {
			/* if present remove current chatboxid from jbolo_open */
			jbolo_open.splice(jbolo_open.indexOf(indx), 1);
			gsCookie("jbolo_open", jbolo_open.join('|'), options);
		}
	}
	/*update jbolo_close cookie*/
	jbolo_close = tmp.getUnique();
	gsCookie("jbolo_close", jbolo_close.join('|'), options);
}

/* This function -
 * - Adds current chatboxid to jbolo_mini cookie,
 * - if present remove current chatboxid from jbolo_close cookie,
 * - if present remove current chatboxid from jbolo_open cookie
 * @param el string e.g. 776_3 [usually uid_nodeid]
 **/
function updateMiniCookie(el) {
	var indx = el;
	var tmp = jbolo_mini.getUnique();
	var options = new Array();
	options['path'] = '/';
	if (el) {
		tmp.push(indx);
		if (include(jbolo_close, indx)) {
			/* if present remove current chatboxid from jbolo_close */
			jbolo_close.splice(jbolo_close.indexOf(indx), 1);
			gsCookie("jbolo_close", jbolo_close.join('|'), options);
		}
		if (include(jbolo_open, indx)) {
			/* if present remove current chatboxid from jbolo_open */
			jbolo_open.splice(jbolo_open.indexOf(indx), 1);
			gsCookie("jbolo_open", jbolo_open.join('|'), options);
		}
	}
	/*update jbolo_mini cookie*/
	jbolo_mini = tmp.getUnique();
	gsCookie("jbolo_mini", jbolo_mini.join('|'), options);
}

/* This function -
 * - Adds current chatboxid to jbolo_open cookie,
 * - if present remove current chatboxid from jbolo_mini cookie,
 * - if present remove current chatboxid from jbolo_close cookie
 * @param el string e.g. 776_3 [usually uid_nodeid]
 **/
function updateOpenCookie(el) {
	var indx = el;
	var tmp = jbolo_open.getUnique();
	var options = new Array();
	options['path'] = '/';
	if (el) {
		tmp.push(indx);
		if (include(jbolo_close, indx)) {
			/* if present remove current chatboxid from jbolo_close */
			jbolo_close.splice(jbolo_close.indexOf(indx), 1);
			gsCookie("jbolo_close", jbolo_close.join('|'), options);
		}
		if (include(jbolo_mini, indx)) {
			/* if present remove current chatboxid from jbolo_mini */
			jbolo_mini.splice(jbolo_mini.indexOf(indx), 1);
			gsCookie("jbolo_mini", jbolo_mini.join('|'), options);
		}
	}
	/*update jbolo_open cookie*/
	jbolo_open = tmp.getUnique();
	gsCookie("jbolo_open", jbolo_open.join('|'), options);
}

/* This-updating box status
 * @param int nid e.g-1
 * @param int sts e.g-1(0=offline,1=avalable)
 **/
function update_boxstatus(nid, sts) {
	if (nid) {
		techjoomla.jQuery("#chatbox_" + nid + ' #statusicon').removeClass();
		techjoomla.jQuery("#chatbox_" + nid + ' #statusicon').addClass('statusicon_' + sts);
		techjoomla.jQuery("#chatbox_" + nid + ' #offline').removeClass();
		techjoomla.jQuery("#chatbox_" + nid + ' #offline').addClass('offline-msg');
	}
}

