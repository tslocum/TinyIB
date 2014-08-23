function quotePost(postID){
	var message_element = document.getElementById("message");
	if (message_element){
		message_element.focus();
		message_element.value += '>>' + postID + "\n";
	}

	return false;
}

document.addEventListener('DOMContentLoaded', function() {
	if(window.location.hash){
		if(window.location.hash.match(/^#q[0-9]+$/i) !== null){
			var quotePostID = window.location.hash.match(/^#q[0-9]+$/i)[0].substr(2);
			if (quotePostID != ''){
				quotePost(quotePostID);
			}
		}
	}
});
