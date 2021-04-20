@extends('tablda.app')

@section('page-title',  settings('app_name'))

@section('content')
    <homepage inline-template :init_link="'{{route('data')}}'">
        <div id="homepage" v-cloak>
            <div class="buttons">
                {{--<a id="about_btn" target="_blank" href="https://www.youtube.com/watch?v=q22_cJ42iaw&list=PLqBhmUFy6vXqfztgefVHFHWitXslyiG1S">--}}
                    {{--<img src={{url("/assets/img/icons/info.png")}}>--}}
                {{--</a>--}}
                {{--<a id="contact_btn" @click.prevent="contactClick()"><img src={{url("/assets/img/icons/mail.png")}}></a>--}}
            </div>

            <div class="dots-wrapper">
                <ul class="dots">
                    <li :class="[hash == '#About' ? 'active' : '']">
                        <a href="#About"></a>
                    </li>
                    <li :class="[hash == '#MorethanExcel' ? 'active' : '']">
                        <a href="#MorethanExcel" class="vs-anchor"></a>
                    </li>
                    <li :class="[hash == '#AllTogether' ? 'active' : '']">
                        <a href="#AllTogether" class="vs-anchor"></a>
                    </li>
                    <li :class="[hash == '#FullControl' ? 'active' : '']">
                        <a href="#FullControl" class="vs-anchor"></a>
                    </li>
                    <li :class="[hash == '#LinkingTables' ? 'active' : '']">
                        <a href="#LinkingTables" class="vs-anchor"></a>
                    </li>
                    <li :class="[hash == '#MuchMore' ? 'active' : '']">
                        <a href="#MuchMore" class="vs-anchor"></a>
                    </li>
                </ul>
            </div>

            <div class="mainbag">
                <div vs-anchor="About" class="mainview">
                    <div class="vs-center-wrap vs-center-wrap--img">
                        <div class="vs-center home-frame vs-center--img">
                            <a :href="data_link" class="vs-a--img">
                                <div class="mob">TablDA mobile access and App are under development. Please visit TablDA through desktop browser.</div>
                                <img src="/assets/img/TabDA Full HD.gif" class="vs--img">
                            </a>
                        </div>
                    </div>
                </div>
                <div vs-anchor="MorethanExcel" class="mainview">
                    <div class="vs-center-wrap">
                        <div class="vs-center home-frame">
                            <a :href="data_link">
                                <div class="home-row">
                                    <div class="home-cell home--sm">
                                        <h1>Like&nbsp;Excel<br>
                                            <span class="txt--white">More&nbsp;Than</span>&nbsp;Excel
                                        </h1>
                                        <h3>
                                            <i>Powerful functions for organizing, searching, filtering, updating, viewing, collecting and sharing tabulated data.</i>
                                        </h3>
                                    </div>
                                    <div class="home-cell home--lg">
                                        <img src="assets/img/moreexcel.png" width="100%">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div vs-anchor="AllTogether" class="mainview">
                    <div class="vs-center-wrap">
                        <div class="vs-center home-frame">
                            <a :href="data_link">
                                <div class="home-row">
                                    <div class="home-cell home--lg">
                                        <img src="assets/img/diagram.png" width="100%">
                                    </div>
                                    <div class="home-cell home--sm">
                                        <h1>
                                            <span class="txt--white">One</span> Dataset<br>
                                            <span class="txt--white">Many</span> Relational Tables<br>
                                            <span class="txt--white">Entire</span> Team<br>
                                            <span class="txt--white">All</span> Together
                                        </h1>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div vs-anchor="FullControl" class="mainview">
                    <div class="vs-center-wrap">
                        <div class="vs-center home-frame">
                            <a :href="data_link">
                                <div class="home-row">
                                    <div class="home-cell home--sm">
                                        <h1>
                                            Table-centered<br>
                                            Work&nbsp;Collaboration<br>
                                            w/ <span class="txt--white">Full</span><br>
                                            Permission Control
                                        </h1>
                                    </div>
                                    <div class="home-cell home--lg">
                                        <img src="assets/img/groups.png" width="100%">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div vs-anchor="LinkingTables" class="mainview">
                    <div class="vs-center-wrap">
                        <div class="vs-center home-frame">
                            <a :href="data_link">
                                <div class="home-row">
                                    <div class="home-cell home--lg">
                                        <img src="assets/img/relations.png" width="100%">
                                    </div>
                                    <div class="home-cell home--sm">
                                        <h1>
                                            Linking Relational Tables<br>
                                            to a <span class="txt--white">Higher</span> Level
                                        </h1>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div vs-anchor="MuchMore" class="mainview">
                    <div class="vs-center-wrap">
                        <div class="vs-center home-frame">
                            <a :href="data_link">
                                <div class="home-row">
                                    <div class="home-cell home--sm txt--left">
                                        <h1>
                                            <span class="txt--white">Much More</span> Than<br>
                                            Just Table and Data
                                        </h1>
                                        <h3>BI, GSI &amp; APPs</h3>
                                    </div>
                                    <div class="home-cell">
                                        <div class="img-wrapper">
                                            <!--<div class="on-img-text">Business&nbsp;Intelligence&nbsp;(BI)<br>Define&nbsp;Your&nbsp;Own&nbsp;Dashboard</div>-->
                                            <img src="assets/img/dashboard-text.png" width="100%">
                                        </div>
                                        <div class="img-wrapper">
                                            <!--<div class="on-img-text on-img-text--bottom">Customizable&nbsp;Geographic Spatial&nbsp;Information&nbsp;(GSI)</div>-->
                                            <img src="assets/img/gsi-text.png" width="100%">
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="isContact" class="modal-form-background" @click.self="isContact = false"></div>
            <div v-show="isContact" class="contact-container">
                <form @submit.prevent="sendForm()">
                    <div class="form-group">
                        <input class="form-control" type="text" v-model="email" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" v-model="subject" placeholder="Subject">
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Message" rows="9" class="form-control" v-model="message"></textarea>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="file" ref="file" @change="handleFileUpload()" placeholder="Attachment">
                    </div>
                    <div>
                        <button type="submit" class="pull-left btn btn-success">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </homepage>
@endsection
