$(document).ready(function() {
    $('#store_reviews').show();
    var reviewsContainer = document.getElementById('store_review_container'),
        url = window.location + 'index.php?route=module/store_reviews/postStoreReview';

    var stars = document.querySelectorAll('.review');
    var originalColor = 'white';
    if (stars[0]&& typeof stars[0].style !== 'undefined' && typeof stars[0].style.color !== 'undefined' ) {
         originalColor = stars[0].style.color;
    }


    // Get values
    stars.forEach(star => {
        star.addEventListener('click', function() { 
           var value = this.getAttribute('data-alt');
           postRatings(url, value);
        });
    });

    // select
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            var index = this.getAttribute('data-alt');

            for(let i = 0; i < index; i++) {
                stars[i].style.color = "gold";
            }
        })
    });

    // unselect
    stars.forEach(star => {
        star.addEventListener('mouseleave', function() {
            var index = this.getAttribute('data-alt');
            for(let i = 0; i < index; i++) {
                this.style.color = originalColor;
            }
        });
    });

    // Store data
    function postRatings(url, rate) {
        var xhr = new XMLHttpRequest();

        if (!xhr) return false;

        xhr.onreadystatechange = function() {
             if (xhr.readyState === XMLHttpRequest.DONE) {
                if(xhr.status === 200) {
                    let res = JSON.parse(xhr.responseText);
                    console.log(res);
                    reviewsContainer.innerHTML = `<p>${res.status}</p>`;
                } else {
                    console.log("ERROR!");
                }
            }
        };

        xhr.open('POST', url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('rate=' + encodeURIComponent(rate));
    }
});
