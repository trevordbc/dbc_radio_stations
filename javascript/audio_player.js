jQuery(document).ready(function($) {
    const player = document.getElementById('player');
    const currentTimeElement = $('#current-time');
    const durationElement = $('#duration');
    const podcastTitleElement = $('#podcast-title');
    const seeker = document.getElementById('seekbar');

    function playPodcast(src, title) {
        player.src = src;
        player.play();
        podcastTitleElement.text(title);
    }

    function resetPlayer() {
        player.pause();
        player.currentTime = 0;
        seeker.value = 0;
    }

    elementorPodcastData.podcasts.forEach((podcast, index) => {
        const accordionTitle = $('.elementor-accordion-title[data-rss-feed="' + podcast.url + '"]');
        const sectionToggle = accordionTitle.data('section-toggle');

        if (userHasAccess(podcast.allowed_roles)) {
            let playlistHtml = '';

            podcast.items.forEach((item, itemIndex) => {
                const duration = formatDuration(item.duration);
                playlistHtml += `
                    <div class="playlist-item" data-src="${item.enclosure}">
                        <div class="item-title">${item.title}</div>
                        <div class="item-duration">${duration}</div>
                    </div>
                `;
            });

            $('#' + sectionToggle + ' .custom-accordion-container').html(playlistHtml);

            $('#' + sectionToggle + ' .playlist-item').on('click', function () {
                const src = $(this).data('src');
                const title = $(this).find('.item-title').text();
                playPodcast(src, title);

                $('.playlist-item').removeClass('playing');
                $(this).addClass('playing');
            });
        }
    });

    player.addEventListener('timeupdate', function () {
        seeker.value = (player.currentTime / player.duration) * 100;
        currentTimeElement.text(formatDuration(player.currentTime));
    });

    seeker.addEventListener('input', function () {
        player.currentTime = (seeker.value / 100) * player.duration;
    });

    player.addEventListener('loadedmetadata', function () {
        durationElement.text(formatDuration(player.duration));
    });

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

    function formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);

        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function userHasAccess(allowedRoles) {
        if (!Array.isArray(allowedRoles) || allowedRoles.length === 0) {
            return true;
        }

        return elementorPodcastData.userRoles.some(role => allowedRoles.includes(role));
    }
	const firstAccordion = $('.elementor-accordion-title').first();
const firstAccordionSection = firstAccordion.data('section-toggle');
const firstAccordionPodcast = elementorPodcastData.podcasts.find(p => p.url === firstAccordion.data('rss-feed'));

if (firstAccordionPodcast && firstAccordionPodcast.items.length > 0) {
    const firstPodcast = firstAccordionPodcast.items[0];
    playPodcast(firstPodcast.enclosure, firstPodcast.title);

    $('#' + firstAccordionSection + ' .playlist-item').first().addClass('playing');
}

$('.elementor-accordion-title').on('click', function() {
    const sectionToggle = $(this).data('section-toggle');
    const isOpen = $('#' + sectionToggle).hasClass('elementor-active');

    if (isOpen) {
        $('#' + sectionToggle + ' .playlist-item').first().click();
    } else {
        resetPlayer();
    }
});
});
