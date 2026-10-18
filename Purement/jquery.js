// Smooth scroll & fade-in animations
$(document).ready(function() {

    // ================= SMOOTH SCROLL =================
    $('a[href^="#"], .book-btn, .staff-portal').on('click', function(e) {
        e.preventDefault();

        let target;
        // If button has a data-target or href
        if ($(this).attr('href')) {
            target = $(this).attr('href');
        } else if ($(this).data('target')) {
            target = $(this).data('target');
        }

        if (target && $(target).length) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - 80 // Sticky header offset
            }, 800);
        }
    });

    // ================= FADE-IN ANIMATION =================
    function fadeInSections() {
        $('.about-container, .menu-section, .experience-section, .contact-section, .hero').each(function() {
            let top_of_element = $(this).offset().top;
            let bottom_of_window = $(window).scrollTop() + $(window).height();

            if (bottom_of_window > top_of_element + 100) {
                $(this).animate({opacity:1, top:0}, 800);
            }
        });
    }

    // Initialize sections as hidden
    $('.about-container, .menu-section, .experience-section, .contact-section, .hero').css({
        opacity: 0,
        position: 'relative',
        top: 50
    });

    // Run on scroll and on load
    $(window).on('scroll load', fadeInSections);
});