<head>
    <script>
        /*
         *
         * Хак для работы сайта в IE при закрытом испекторе
         *
         * Каким-то неведомым образом, пока инспектор закрыт
         * window.console - это пустой объект.
         *
         * https://stackoverflow.com/a/52921595
         *
         * IE - гори в аду!
         *
         * */
        if (!window.console || Object.keys(window.console).length === 0) {
            window.console = {
                log: function() {},
                info: function() {},
                error: function() {},
                warn: function() {}
            };
        }
        window.addEventListener("load", () => {
            document.documentElement.classList.add('font-material-icons-loaded');
        });
        /** https://stackoverflow.com/a/60138011/1760643 */
        // Check if API exists
        /** if (document && document.fonts) {
          // Do not block page loading
          setTimeout(function () {
            document.fonts.load('24px "Material Icons"').then(() => {
              // Make font using elements visible
              document.documentElement.classList.add('font-material-icons-loaded')
            })
          }, 0)
        } else {
          // Fallback if API does not exist
          document.documentElement.classList.add('font-material-icons-loaded')
        } */
    </script>
    <link href="/fonts/materialicons/material-icons.css" media="screen" rel="stylesheet" type="text/css" >
	<style>
        @media screen {
            html:not(.font-material-icons-loaded) .material-icons {
                opacity: 0;
            }
        }

        @media screen and (-ms-high-contrast: active), screen and (-ms-high-contrast: none) {
            .v-btn::before {
                border-radius: 0 !important;
            }
            .v-btn--icon::before, .v-btn--floating::before {
                border-radius: 50% !important;
            }
            .v-btn::before {
                transition: none !important;
            }
            .v-main, .v-navigation-drawer, .v-toolbar {
                -webkit-transition: none !important;
                transition: none !important;
                padding-right: 0px !important;
            }
            .v-main * {
                -webkit-transition: none !important;
                transition: none !important;
            }
        }

		[v-cloak] {
			visibility: hidden;
		}
		.shine-default {
			-webkit-transition: -webkit-box-shadow .3s cubic-bezier(.25,.8,.5,1);
			transition: box-shadow .3s cubic-bezier(.25,.8,.5,1),color 1ms;
			margin: 6px;
			border-radius: 50%;
		}
		.shine {
			-webkit-box-shadow: 0 5px 5px -3px rgba(0,0,0,.2), 0 8px 10px 1px rgba(0,0,0,.14), 0 3px 14px 2px rgba(0,0,0,.12);
			box-shadow: 0 5px 5px -3px rgba(0,0,0,.2), 0 8px 10px 1px rgba(0,0,0,.14), 0 3px 14px 2px rgba(0,0,0,.12);
		}
		.pr-0 .v-toolbar__content {
			padding-right: 16px;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans Light"), local("Open-Sans-Light"),
			url("/fonts/OpenSans/OpenSans-Light.eot"),
			url("/fonts/OpenSans/OpenSans-Light.eot?#iefix") format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-Light.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-Light.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-Light.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-Light.svg#open-sans") format("svg");
			font-weight: 200;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans Light italic"), local("Open-Sans-Light-Italic"),
			url("/fonts/OpenSans/OpenSans-LightItalic.eot"),
			url("/fonts/OpenSans/OpenSans-LightItalic.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-LightItalic.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-LightItalic.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-LightItalic.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-LightItalic.svg#open-sans") format("svg");
			font-weight: 200;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans Regular"), local("Open-Sans-Regular"),
			url("/fonts/OpenSans/OpenSans-Regular.eot"),
			url("/fonts/OpenSans/OpenSans-Regular.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-Regular.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-Regular.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-Regular.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-Regular.svg#open-sans") format("svg");
			font-weight: 400;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans Italic"), local("Open-Sans-Italic"),
			url("/fonts/OpenSans/OpenSans-Italic.eot"),
			url("/fonts/OpenSans/OpenSans-Italic.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-Italic.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-Italic.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-Italic.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-Italic.svg#open-sans") format("svg");
			font-weight: 400;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans SemiBold"), local("Open-Sans-SemiBold"),
			url("/fonts/OpenSans/OpenSans-SemiBold.eot"),
			url("/fonts/OpenSans/OpenSans-SemiBold.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-SemiBold.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-SemiBold.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-SemiBold.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-SemiBold.svg#open-sans") format("svg");
			font-weight: 600;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans SemiBoldItalic"), local("Open-Sans-SemiBoldItalic"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.eot"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-SemiBoldItalic.svg#open-sans") format("svg");
			font-weight: 600;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans Bold"), local("Open-Sans-Bold"),
			url("/fonts/OpenSans/OpenSans-Bold.eot"),
			url("/fonts/OpenSans/OpenSans-Bold.eot?#iefix") format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-Bold.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-Bold.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-Bold.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-Bold.svg#open-sans") format("svg");
			font-weight: 700;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans BoldItalic"), local("Open-Sans-BoldItalic"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.eot"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-BoldItalic.svg#open-sans") format("svg");
			font-weight: 700;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans ExtraBold"), local("Open-Sans-ExtraBold"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.eot"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-ExtraBold.svg#open-sans") format("svg");
			font-weight: 800;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: "Open Sans";
			src: local("Open Sans ExtraBoldItalic"), local("Open-Sans-ExtraBoldItalic"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.eot"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.eot?#iefix")
			format("embedded-opentype"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.woff") format("woff2"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.woff") format("woff"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.ttf") format("truetype"),
			url("/fonts/OpenSans/OpenSans-ExtraBoldItalic.svg#open-sans") format("svg");
			font-weight: 800;
			font-style: italic;
			font-display: swap;
		}

        /* This stylesheet generated by Transfonter (https://transfonter.org) on February 25, 2018 4:00 PM */

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-MediumItalic.eot');
            src: local('Roboto Medium Italic'), local('Roboto-MediumItalic'),
            url('/fonts/Roboto/Roboto-MediumItalic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-MediumItalic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-MediumItalic.ttf') format('truetype');
            font-weight: 500;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Italic.eot');
            src: local('Roboto Italic'), local('Roboto-Italic'),
            url('/fonts/Roboto/Roboto-Italic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Italic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Italic.ttf') format('truetype');
            font-weight: normal;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Bold.eot');
            src: local('Roboto Bold'), local('Roboto-Bold'),
            url('/fonts/Roboto/Roboto-Bold.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Bold.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Regular.eot');
            src: local('Roboto'), local('Roboto-Regular'),
            url('/fonts/Roboto/Roboto-Regular.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Regular.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Medium.eot');
            src: local('Roboto Medium'), local('Roboto-Medium'),
            url('/fonts/Roboto/Roboto-Medium.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Medium.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Medium.ttf') format('truetype');
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-BoldItalic.eot');
            src: local('Roboto Bold Italic'), local('Roboto-BoldItalic'),
            url('/fonts/Roboto/Roboto-BoldItalic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-BoldItalic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-BoldItalic.ttf') format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-ThinItalic.eot');
            src: local('Roboto Thin Italic'), local('Roboto-ThinItalic'),
            url('/fonts/Roboto/Roboto-ThinItalic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-ThinItalic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-ThinItalic.ttf') format('truetype');
            font-weight: 100;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Black.eot');
            src: local('Roboto Black'), local('Roboto-Black'),
            url('/fonts/Roboto/Roboto-Black.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Black.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Black.ttf') format('truetype');
            font-weight: 900;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Light.eot');
            src: local('Roboto Light'), local('Roboto-Light'),
            url('/fonts/Roboto/Roboto-Light.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Light.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Light.ttf') format('truetype');
            font-weight: 300;
            font-style: normal;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-LightItalic.eot');
            src: local('Roboto Light Italic'), local('Roboto-LightItalic'),
            url('/fonts/Roboto/Roboto-LightItalic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-LightItalic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-LightItalic.ttf') format('truetype');
            font-weight: 300;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-BlackItalic.eot');
            src: local('Roboto Black Italic'), local('Roboto-BlackItalic'),
            url('/fonts/Roboto/Roboto-BlackItalic.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-BlackItalic.woff') format('woff'),
            url('/fonts/Roboto/Roboto-BlackItalic.ttf') format('truetype');
            font-weight: 900;
            font-style: italic;
        }

        @font-face {
            font-family: 'Roboto';
            src: url('/fonts/Roboto/Roboto-Thin.eot');
            src: local('Roboto Thin'), local('Roboto-Thin'),
            url('/fonts/Roboto/Roboto-Thin.eot?#iefix') format('embedded-opentype'),
            url('/fonts/Roboto/Roboto-Thin.woff') format('woff'),
            url('/fonts/Roboto/Roboto-Thin.ttf') format('truetype');
            font-weight: 100;
            font-style: normal;
        }


		body {
			/*font-family: "Open Sans", -apple-system , BlinkMacSystemFont , Segoe UI, Helvetica, Arial ,sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;*/
			font-family: "Roboto", "Open Sans", -apple-system , BlinkMacSystemFont , Segoe UI, Helvetica, Arial ,sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
		}

		.loader {
			display: none;
		}
		.loader[v-cloak] {
			color: black;
			display: block;
			position: fixed;
			top: 0;
			bottom: 0;
			left: 0;
			right: 0;
			background-color: #fafafa;
			visibility: visible;
			z-index: 1000;
		}
	</style>
	<script>
		// дефолтные цвета только для сравнения
		if (!window.__HM) {
            window.__HM = {};
        }
		// TODO проверить, так ли для vuetify 2

		// Цвета бренда из design.ini
        <?php
            $themeColors = $this->getDesignSetting('themeColors');
            $darkTheme = $this->getDesignSetting('darkTheme');

            if ($themeColors) {
                echo 'window.__HM["themeColors"] = ' . json_encode($themeColors) . ' ;';
            }
            if ($darkTheme) {
                echo 'window.__HM["darkTheme"] = ' . json_encode($darkTheme) . ' ;';
            }
        ?>
        const colors = <?php echo HM_Json::encodeErrorSkip($this->getDesignSetting('skinColors')); ?>;
        if(colors) {
            window.__HM.themeColors = colors;
        }
	</script>
    <?php echo $this->headTitle(); ?>
	<?php echo $this->headMeta(); ?>
	<?php echo $this->headLink(); ?>

    <link href="<?php echo $this->publicFileToUrlWithHash('/css/jquery-compatibility.css'); ?>" media="screen" rel="stylesheet" type="text/css" />

    <link rel="favicon" href="/favicon.ico">

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/apple-touch-icon-167x167.png">

    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
    <link rel="manifest" href="/manifest.json">

</head>
