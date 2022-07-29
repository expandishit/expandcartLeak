self.addEventListener('message', function(e) {
  	var data = e.data;


	if(typeof(EventSource) !== "undefined") {
		const evtSource = new EventSource(data.url);

		evtSource.addEventListener("new_bid", e =>{	

		  const data = JSON.parse(e.data);
		  //update current bid value..
		  //update next minimum bid..
		  //update bidders list..
		  self.postMessage({
		  	'bid': data.current_bid, 
		  	'next_minimum_allowed_bid' : data.next_minimum_allowed_bid,
		  	'bidders' : data.bidders
		  });

		}, false);

		evtSource.onerror = err =>{
		    console.error("EventSource failed:", err);
			self.postMessage({'error': 'An error occurred while attempting to connect.'});
			evtSource.close();
		};

	} else {
		//If browser doesn't support SSE
		self.postMessage({'error': "Please change your browser..."});
	}

}, false);


