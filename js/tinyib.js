function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function storePassword() {
	var newpostpassword = document.getElementById("newpostpassword");
	if (newpostpassword) {
		var expiration_date = new Date();
		expiration_date.setFullYear(expiration_date.getFullYear() + 7);
		document.cookie = "tinyib_password=" + encodeURIComponent(newpostpassword.value) + "; path=/; expires=" + expiration_date.toGMTString();
	}
}

function quotePost(postID) {
	var message_element = document.getElementById("message");
	if (message_element) {
		message_element.focus();
		message_element.value += '>>' + postID + "\n";
	}

	return false;
}

document.addEventListener('DOMContentLoaded', function () {
	var newpostpassword = document.getElementById("newpostpassword");
	if (newpostpassword) {
		newpostpassword.addEventListener("change", storePassword);
	}

	var password = getCookie("tinyib_password");
	if (password && password != "") {
		if (newpostpassword) {
			newpostpassword.value = password;
		}

		var deletepostpassword = document.getElementById("deletepostpassword");
		if (deletepostpassword) {
			deletepostpassword.value = password;
		}
	}

	if (window.location.hash) {
		if (window.location.hash.match(/^#q[0-9]+$/i) !== null) {
			var quotePostID = window.location.hash.match(/^#q[0-9]+$/i)[0].substr(2);
			if (quotePostID != '') {
				quotePost(quotePostID);
			}
		}
	}
});
