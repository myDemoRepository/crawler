<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                font-weight: 100;
                height: 50vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .block-center {
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            #results td {
                border: 1px solid #ccc;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <div class="block-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    <p>Crawler results</p>
                </div>
                <div class="block-center">
                    <table id="results">
                        <tr>
                            <td>Domain</td>
                            <td>Img Tags Count</td>
                            <td>Page Processing Time(sec.)</td>
                        </tr>
                        @foreach($parserData as $key => $value)
                            <tr>
                                @foreach($value as $v)
                                    <td>{{ $v }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
                </div>

            </div>
        </div>
    </body>
</html>
