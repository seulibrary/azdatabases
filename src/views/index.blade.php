<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AZ Database</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <style>
        body {
            background: #5F9EA0;
        }
        a {
            color: white;
        }
        .container {
            padding: 2em 0;
        }
        #results > li {
            background: rgba(255,255,255,.1);
            margin-bottom: 1em;
            padding: .5em;
            
        }
    </style>
    </head>
    <body>
    <div id="app" class="container">

        <div class="row">
            <div class="col-lg-12">
                <search-component></search-component>    
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center lead">
                @foreach (range('A', 'Z') as $letter)
                    <router-link to="/az/{{ $letter }}">{{ $letter }}</router-link>
                @endforeach
                &nbsp;|&nbsp;
                <router-link to="/">View All Databases</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                @include('vendor.azdatabases.aznav', ['navigation' => $navigation])
            </div>
            <div class="col-md-8">
                <router-view></router-view>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('/assets/js/azdatabases.js') }}"></script>
    </body>
</html>
