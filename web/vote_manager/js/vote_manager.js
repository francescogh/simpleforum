$(".btn-vote-pos,.btn-vote-neg").click(function(){
	
	// thread_id and vote details are stored in the button attribute id
	var btnFields  = $(this).attr("id").split("-");
	
	var vote = btnFields[0] == "bvp" ? 1 : -1;
	if ($(this).hasClass('on')) vote = 0;
	
	var voteUrl = btnFields[1] == 'thread' ? voteThreadUrl : votePostUrl;
	
	var threadId = btnFields[2];	
	
	$.get( voteUrl, {id: threadId, vote: vote} )
	.done(function( data ) {
	    if (data.result == "success")
	    {
	    	// update score text
	    	$("#score-"+btnFields[1]+"-"+threadId).text(data.data.score);
	    	
	    	// print vote message
	    	$("#vm-"+btnFields[1]+"-"+threadId).text(data.data.voteMessage);
	    	
	    	// update positive vote button class
	    	var bvpState = (data.data.userStoredVote == '1') ? "on" : "off";
	    	$("#bvp-"+btnFields[1]+"-"+threadId).removeClass().addClass("btn-vote-pos "+bvpState);
	    	
	    	// update negative vote button class
	    	var bvnState = (data.data.userStoredVote == '-1') ? "on" : "off";
	    	$("#bvn-"+btnFields[1]+"-"+threadId).removeClass().addClass("btn-vote-neg "+bvnState);
	    }
	});
});