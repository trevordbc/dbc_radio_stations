jQuery(document).ready(function ($) {
	if (typeof elementorPodcastData !== "undefined") {
    const player = document.getElementById('player');
    const seeker = document.getElementById('seekbar');
    const currentTimeElement = document.getElementById('current-time');
    const durationElement = document.getElementById('duration');
    const podcastTitleElement = document.getElementById('podcast-title');
    const playingIndicator = document.getElementById('playing-indicator');
    const pagination = document.getElementById('pagination');
    let currentPlayingItem = null;

    function resetPlayer() {
        player.pause();
        player.src = '';
        seeker.value = 0;
        currentTimeElement.textContent = '00:00';
        durationElement.textContent = '00:00';
    }

    function playPodcast(enclosure, title) {
        resetPlayer();
        player.src = enclosure;
        podcastTitleElement.textContent = title;
        player.play();
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function displayPlaylistItems(podcast) {
        if (!podcast || !podcast.items || podcast.items.length === 0) {
            return '';
        }

        return podcast.items.map((item, index) => {
            const formattedDuration = formatTime(item.duration);
            return `<div class="playlist-item" data-src="${item.enclosure}" data-index="${index}">
                <div class="podcast-title">${item.title}</div>
                <div class="podcast-duration">${formattedDuration}</div>
            </div>`;
        }).join('');
    }

    elementorPodcastData.podcasts.forEach((podcast, index) => {
        const accordionContent = $(`.elementor-accordion .elementor-accordion-item:eq(${index}) .custom-accordion-container`);
        accordionContent.html(displayPlaylistItems(podcast));
    });

    // Play, Pause, and Stop controls
    $('#play').on('click', function () {
        player.play();
    });

    $('#pause').on('click', function () {
        player.pause();
    });

    $('#stop').on('click', function () {
        resetPlayer();
    });

    // Seeker and time updates
    player.addEventListener('timeupdate', function () {
        seeker.value = player.currentTime / player.duration * 100;
        currentTimeElement.textContent = formatTime(player.currentTime);
    });

    seeker.addEventListener('input', function () {
        player.currentTime = player.duration * (seeker.value / 100);
    });

    player.addEventListener('loadedmetadata', function () {
        durationElement.textContent = formatTime(player.duration);
    });

    // Playlist items click
    $('.elementor-accordion').on('click', '.playlist-item', function () {
        if (currentPlayingItem) {
            currentPlayingItem.removeClass('playing');
        }
        currentPlayingItem = $(this);
        currentPlayingItem.addClass('playing');

        const src = $(this).data('src');
        const title = $(this).find('.podcast-title').text();
        playPodcast(src, title);
    });

    // Initialize the first podcast
    const firstAccordion = $('.elementor-accordion-title').first();
    const firstAccordionSection = firstAccordion.data('section-toggle');
    const firstAccordionPodcast = elementorPodcastData.podcasts.find(p => p.url === firstAccordion.data('rss-feed'));
	
	if (firstAccordionPodcast && firstAccordionPodcast.items.length > 0) {
        const firstPodcast = firstAccordionPodcast.items[0];
        podcastTitleElement.textContent = firstPodcast.title;
        player.src = firstPodcast.enclosure;
        currentPlayingItem = $(`.elementor-accordion .elementor-accordion-item:eq(0) .playlist-item[data-index="0"]`);
        currentPlayingItem.addClass('playing');
    }

    // Automatically move to the next podcast when one ends
    player.addEventListener('ended', function () {
        if (!currentPlayingItem) {
            return;
        }

        const nextIndex = currentPlayingItem.data('index') + 1;
        const accordionIndex = $('.elementor-accordion .elementor-accordion-item').index(currentPlayingItem.closest('.elementor-accordion-item'));
        const nextPlayingItem = $(`.elementor-accordion .elementor-accordion-item:eq(${accordionIndex}) .playlist-item[data-index="${nextIndex}"]`);

        if (nextPlayingItem.length > 0) {
            currentPlayingItem.removeClass('playing');
            currentPlayingItem = nextPlayingItem;
            currentPlayingItem.addClass('playing');

            const src = nextPlayingItem.data('src');
            const title = nextPlayingItem.find('.podcast-title').text();
            playPodcast(src, title);
        } else {
            resetPlayer();
        }
    });

    // Set the first accordion as active
    firstAccordion.trigger('click');
	}
});
