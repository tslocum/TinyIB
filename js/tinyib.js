function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function quotePost(postID) {
	$("#message").val('>>' + postID + "\n").focus();

	return false;
}

function reloadCAPTCHA() {
	$("#captcha").val("").focus();
	$("#captchaimage").attr("src", $("#captchaimage").attr("src") + "#new")

	return false;
}

function showEmbed(id, embedhtml){
	if($("#thumbembed"+ id).attr('expanded') != 'true') {
		$("#thumbembed"+ id).hide();
		$("#embed"+ id).show();
		$("#embed"+ id).html(embedhtml);
		$("#thumbembed"+ id).attr('expanded', 'true');
	}else{
		$("#embed"+ id).hide();
		$("#embed"+ id).html('');
		$("#thumbembed"+ id).show();
		$("#thumbembed"+ id).attr('expanded', 'false');
	}
}

$(function() {
	var newpostpassword = $("#newpostpassword");
	if (newpostpassword) {
		newpostpassword.change(function () {
			var newpostpassword = $("#newpostpassword");
			if (newpostpassword) {
				var expiration_date = new Date();
				expiration_date.setFullYear(expiration_date.getFullYear() + 7);
				document.cookie = "tinyib_password=" + encodeURIComponent(newpostpassword.val()) + "; path=/; expires=" + expiration_date.toGMTString();
			}
		});
	}

	var password = getCookie("tinyib_password");
	if (password && password != "") {
		if (newpostpassword) {
			newpostpassword.val(password);
		}

		var deletepostpassword = $("#deletepostpassword");
		if (deletepostpassword) {
			deletepostpassword.val(password);
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
