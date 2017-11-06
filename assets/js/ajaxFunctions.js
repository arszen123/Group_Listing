function loadFeed(url,data,printResultTo){
	$.ajax({
		url: url+'?'+data,
		success: function(result){
			$(printResultTo).html(result);
			offset++;
			ajaxDone = 1;
			getNumberOfPosts(data);
		}
	});
}
function appendFeed(url,data,printResultTo){
	$.ajax({
		url: url+'?'+data,
		success: function(result){
			$(printResultTo).append(result);
			offset++;
			ajaxDone = 1;
		}
	});
}
function getNumberOfPosts(data){
	$.ajax({
		url: 'ajax/getNumberOfPosts.php?'+data,
		success: function(result){
			$("#numberOfPosts").html(result);
		}
	});
}