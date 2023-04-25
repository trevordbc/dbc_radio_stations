jQuery(document).ready(function($) {
    var player = $('#player')[0];
    var playBtn = $('#play');
    var pauseBtn = $('#pause').hide();
    var stopBtn = $('#stop');
    var seekbar = $('#seekbar');
    var currentTimeDisplay = $('#current-time');
    var durationDisplay = $('#duration');
    var playingTitle = $('#playing-title');
    var audioLimit = parseInt(player.dataset.audioLimit);

    function formatTime(seconds) {
        var minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return minutes + ":" + (seconds < 10 ? "0" + seconds : seconds);
    }

    player.addEventListener('timeupdate', function() {
        currentTimeDisplay.text(formatTime(player.currentTime));
        durationDisplay.text(formatTime(player.duration));
        seekbar.val(100 * player.currentTime / player.duration);

        // Move the current time element with the seeker
        const seekerWidth = $('#seekbar').width();
        const percentage = (player.currentTime / player.duration) * 100;
        const currentTimePosition = (seekerWidth * percentage) / 100;
        $('#current-time').css('left', `${currentTimePosition}px`);
    });

    player.addEventListener('loadedmetadata', function() {
        durationDisplay.text(formatTime(player.duration));
    });

    seekbar.on('input', function() {
        player.currentTime = player.duration * seekbar.val() / 100;
    });

    function resetPlayer() {
        player.pause();
        player.currentTime = 0;
        playBtn.show();
        pauseBtn.hide();
    }

    playBtn.on('click', function() {
        player.play();
        playBtn.hide();
        pauseBtn.show();
    });

    pauseBtn.on('click', function() {
        player.pause();
        pauseBtn.hide();
        playBtn.show();
    });

    stopBtn.on('click', resetPlayer);

    // Create a function to update the playing title
    function updatePlayingTitle(title) {
        playingTitle.text(title);
    }

    // Create a function to handle playing a new podcast
    function playPodcast(src, title) {
        player.src = src;
        player.load();
        player.play();
        playBtn.hide();
        pauseBtn.show();
        updatePlayingTitle(title);
    }

    // Add event listeners for the accordion titles to load the playlist
    $('.elementor-accordion-title').on('click', function () {
        const section = $(this).data('section-toggle');
        const rssFeed = $(this).data('rss-feed');
        const podcast = elementorPodcastData.podcasts.find(p => p.url === rssFeed);

        if (!podcast) return;

        if (podcast.protectedcontent === 'yes' && !userHasAccess(podcast.allowed_roles)) {
            $('#' + section + ' #playlist').html('<p>Access denied. You don\'t have permission to view this content.</p>');
        } else {
            let html = '';
            podcast.items.forEach(item => {
                html += `
                    <div class="playlist-item" data-src="${item.enclosure}">
                        <span class="item-title">${item.title}</span>
                        <span class="item-duration">${formatTime(item.duration)}</span>
                    </div>
                `;
            });

            $('#' + section + ' #playlist').html(html);

            // Add event listeners for the playlist items to load the audio source
            $('.playlist-item').on('click', function() {
                const src = $(this).data('src');
                const title = $(this).find('.item-title').text();
                playPodcast(src, title);

                // Add "Playing" text to the currently playing podcast
                $('.playlist-item').removeClass('playing');
                $(this).addClass('playing');
            });
        }
    });

    // Automatically play the next podcast when the current one ends
    player.addEventListener('ended', function() {
        let currentPodcast = $('.playlist-item.playing');
        let nextPodcast = currentPodcast.next('.playlist-item');

        if (nextPodcast.length === 0) {
            resetPlayer();
        } else {
            const src = nextPodcast.data('src');
            const title = nextPodcast.find('.item-title').text();
            playPodcast(src, title);
            currentPodcast.removeClass('playing');
            nextPodcast.addClass('playing');
        }
    });

    function userHasAccess(allowedRoles) {
        if (!Array.isArray(allowedRoles) || allowedRoles.length === 0) {
            return true;
        }

        return elementorPodcastData.userRoles.some(role => allowedRoles.includes(role));
    }

    // Automatically load and play the first podcast in the first accordion
    const firstAccordion = $('.elementor-accordion-title').first();
    const firstAccordionSection = firstAccordion.data('section-toggle');
    const firstAccordionPodcast = elementorPodcastData.podcasts.find(p => p.url === firstAccordion.data('rss-feed'));

    if (firstAccordionPodcast && firstAccordionPodcast.items.length > 0) {
        const firstPodcast = firstAccordionPodcast.items[0];
        playPodcast(firstPodcast.enclosure, firstPodcast.title);
        $('#' + firstAccordionSection + ' .playlist-item').first().addClass('playing');
    }

});
