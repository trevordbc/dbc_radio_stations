jQuery(document).ready(function($) {
    var player = $('#player')[0];
    var playBtn = $('#play');
    var pauseBtn = $('#pause').hide();
    var stopBtn = $('#stop');
    var seekbar = $('#seekbar');
    var currentTimeDisplay = $('#current-time');
    var durationDisplay = $('#duration');
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

    // Add event listeners for the accordion titles to load the playlist
    $('body').on('click', '.accordion-title', function () {
        const index = $(this).data('index');
        const podcast = elementorPodcastData.podcasts[index];

        if (!podcast) return;

        if (podcast.protectedcontent === 'yes' && !userHasAccess(podcast.allowed_roles)) {
            $('.accordion-content.accordion-' + index + '-content').html('<p>Access denied. You don\'t have permission to view this content.</p>');
        } else {
            let html = '';
            podcast.items.forEach(item => {
                html += `
                    <div class="playlist-item" data-src="${item.enclosure}">
                        <span class="title">${item.title}</span>
                    </div>
                `;
            });

            $('.accordion-content.accordion-' + index + '-content').html(html);

            // Automatically play the first podcast item when a podcast title is clicked
            player.src = podcast.items[0].enclosure;
            player.load();
            player.play();
            playBtn.hide();
            pauseBtn.show();

            // Add event listeners for the playlist items to load the audio source
            $('.playlist-item').off('click').on('click', function () {
                const src = $(this).data('src');
                player.src = src;
                player.load();
                player.play();
                playBtn.hide();
                pauseBtn.show();
            });
        }
    });

    // The modified code for generating the new HTML structure
    let titlesHtml = '';
    let contentsHtml = '';

    elementorPodcastData.podcasts.forEach((podcast, index) => {
        titlesHtml += `<div class="accordion-title accordion-${index}" data-index="${index}">${podcast.rssfeedtitle}</div>`;

        let itemListHtml = '';
        podcast.items.forEach(item => {
            itemListHtml += `
                <div class="playlist-item" data-src="${item.enclosure}">
                    <span class="title">${item.title}</span>
                </div>
            `;
        });

        const contentClass = index === 0 ? 'accordion-content active' : 'accordion-content';
        contentsHtml += `<div class="${contentClass} accordion-${index}-content">${itemListHtml}</div>`;
    });

    const customAccordionHtml = `
        <div class="custom-accordion">
            <div class="custom-accordion-titles">${titlesHtml}</div>
            <div class="custom-accordion-contents">${contentsHtml}</div>
        </div>
    `;

    // Append the custom accordion to a container
    $('.custom-accordion-container').html(customAccordionHtml);

    // Add event listeners for the accordion titles to toggle the accordion content
    $('.accordion-title').on('click', function() {
        const index = $(this).data('index');
        $('.accordion-content').removeClass('active');
        $('.accordion-' + index + '-content').addClass('active');
    });

    // Add event listeners for the playlist items to load the audio source
    $('.playlist-item').on('click', function() {
        const src = $(this).data('src');
        player.src = src;
        player.load();
        player.play();
        playBtn.hide();
        pauseBtn.show();
    });
});
