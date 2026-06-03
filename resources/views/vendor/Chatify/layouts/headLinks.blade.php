<title>{{ config('chatify.name') }}</title>

{{-- Meta tags --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="id" content="{{ $id }}">
<meta name="messenger-color" content="{{ $messengerColor }}">
<meta name="messenger-theme" content="{{ $dark_mode }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('').'/'.config('chatify.routes.prefix') }}" data-user="{{ Auth::user()->id }}">

{{-- scripts --}}
<script
  src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/chatify/font.awesome.min.js') }}"></script>
<script src="{{ asset('js/chatify/autosize.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>

{{-- styles --}}
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css'/>
<link href="{{ asset('css/chatify/style.css') }}" rel="stylesheet" />
<link href="{{ asset('css/chatify/'.$dark_mode.'.mode.css') }}" rel="stylesheet" />
<link href="{{ asset('css/app.css') }}" rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

{{-- Setting messenger primary color to css --}}
<style>
    :root {
        --primary-color: #ff5d2b !important; /* Marketplace Coral Orange */
    }

    /* Marketplace Theme Overrides */
    body, .messenger, .messenger-listView, .messenger-messagingView, .messenger-infoView, .m-header, .messenger-listView-tabs, .m-body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f9f6f5 !important;
        color: #2f2f2e !important;
    }

    .messenger-listView, .messenger-infoView {
        border-color: #ebdcd5 !important;
    }

    .m-header {
        border-bottom: 1px solid #ebdcd5 !important;
    }

    .messenger-headTitle {
        color: #ff5d2b !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
    }

    .messenger-search {
        background-color: #ffffff !important;
        border: 1px solid #ebdcd5 !important;
        color: #2f2f2e !important;
        border-radius: 9999px !important;
        padding: 10px 16px !important;
    }

    .messenger-search:focus {
        border-color: #ff5d2b !important;
        outline: none;
    }

    .messenger-list-item {
        margin: 4px 8px !important;
        border-radius: 12px !important;
        transition: all 0.2s ease !important;
    }

    .messenger-list-item:hover {
        background-color: #f3eae6 !important;
    }

    .m-list-active, .m-list-active:hover {
        background-color: #ffebe5 !important;
    }

    .m-list-active td {
        color: #ff5d2b !important;
    }

    /* List item text contrast fixes */
    .messenger-list-item td p {
        color: #2f2f2e !important;
        font-weight: 600 !important;
    }

    .messenger-list-item td p span {
        color: #8c7e7a !important; /* soft dark grey for time */
    }

    .messenger-list-item td span {
        color: #5c4e4a !important; /* dark grey for last message preview */
    }

    .messenger-list-item td span .lastMessageIndicator {
        color: #ff5d2b !important; /* Coral for "You:" indicator */
    }

    .m-list-active td p,
    .m-list-active td p span,
    .m-list-active td span,
    .m-list-active td span .lastMessageIndicator {
        color: #ff5d2b !important;
    }

    /* Messages area and bubbles */
    .messages-container {
        background-color: #fdfbfb !important;
    }

    .message-card .message {
        border-radius: 16px !important;
        font-size: 14px !important;
        padding: 10px 16px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    .message-card.mc-sender .message {
        background-color: #ff5d2b !important;
        color: #ffffff !important;
        border-top-right-radius: 4px !important;
    }

    .message-card:not(.mc-sender) .message {
        background-color: #ffffff !important;
        color: #2f2f2e !important;
        border-top-left-radius: 4px !important;
        border: 1px solid #ebdcd5 !important;
    }

    /* Chat send form card */
    .messenger-sendCard {
        background-color: #f9f6f5 !important;
        border-top: 1px solid #ebdcd5 !important;
    }

    .messenger-sendCard form {
        background-color: #ffffff !important;
        border: 1px solid #ebdcd5 !important;
        border-radius: 24px !important;
        padding: 6px 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
    }

    .messenger-sendCard form:focus-within {
        border-color: #ff5d2b !important;
    }

    .messenger-sendCard form textarea {
        background: transparent !important;
        color: #2f2f2e !important;
    }

    .messenger-sendCard form button {
        color: #ff5d2b !important;
        transition: transform 0.2s ease !important;
    }

    .messenger-sendCard form button:hover {
        transform: scale(1.1);
        color: #e04a1b !important;
    }

    .emoji-button {
        color: #a03739 !important; /* Secondary Deep Red color */
    }

    .activeStatus {
        background: #4CAF50 !important;
        border: 2px solid #f9f6f5 !important;
    }

    /* Header Icon & Navigation Styles */
    .m-header-messaging nav a,
    .show-listView {
        color: #ff5d2b !important;
        font-size: 18px !important;
        transition: transform 0.2s ease, opacity 0.2s ease !important;
        text-decoration: none !important;
    }

    .m-header-messaging nav a:hover,
    .show-listView:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }

    /* Make back to home button look like a beautiful pill link */
    .m-header-right a[href="/"] {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background-color: #ffebe5 !important;
        color: #ff5d2b !important;
        padding: 6px 14px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    .m-header-right a[href="/"]::after {
        content: " Beranda" !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    /* Force back button to show on mobile & tablet viewports */
    @media (max-width: 980px) {
        .show-listView {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: #ffebe5 !important;
            color: #ff5d2b !important;
            width: 36px !important;
            height: 36px !important;
            border-radius: 50% !important;
            margin-right: 12px !important;
            font-size: 15px !important;
        }

        .messenger-listView {
            width: 100% !important;
            max-width: unset !important;
        }
    }
</style>
