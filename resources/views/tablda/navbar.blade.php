<navbar-data inline-template
             id="main_navbar"
             class="navbar navbar-default navbar-static-top"
             style="z-index: {{ Route::currentRouteName() != 'homepage' ? 'initial' : 1 }};"
             v-cloak
             v-on:show_login="show_login++"
>
    <div class="container-fluid" :style="$root.themeTopBgStyle">
        <div class="navbar-header">
            <a class="navbar-brand mr-0 no-padding {{ Route::currentRouteName() == 'homepage' ? 'navbar-brand-gif' : '' }}"
               href="{{ Route::currentRouteName() != 'homepage' ? $app_url : route('data') }}"
            >
                <img src="{{ Route::currentRouteName() != 'homepage' ? url('assets/img/TablDA_w_text.png') : url('assets/img/TablDA_w_text.png') }}"
                     height="50"
                     alt="{{ settings('app_name') }}">
            </a>
            @if(in_array($route_group, ['getstarted','homepage']))
                <img src="{{ url('assets/img/Slogan.png') }}" height="50" alt="{{ settings('app_name') }}">
            @endif
            <saving-message :msg_type="$root.sm_msg_type"></saving-message>
        </div>

        <folder-icons-path id="folder-icon-array" :icons-array="iconsArray"></folder-icons-path>

        <div id="navbar" class="navbar-collapse full-height">
            <ul class="nav navbar-nav navbar-right full-height flex flex--center flex--automargin">
                @if($route_group == 'getstarted')
                    <li class="not-mob">
                        <a href="{{ route('discourse-redirect') }}" target="_blank" style="padding-left: 0;padding-right: 0;">
                            <img src="{{ url('/assets/img/icons/discouse_community.png') }}" height="28">
                        </a>
                    </li>
                @endif

                @if($route_group == 'getstarted')
                    <li class="not-mob" id="twitter-follow" style="max-height: 22px;padding-top: 1px;background-color: #1b95e0;border-radius: 3px;"></li>
                    <li class="not-mob" id="linkedin-follow" style=""></li>
                @else
                    <div id="twitter-follow" style="display: none;"></div>
                    <div id="linkedin-follow" style="display: none;"></div>
                @endif

                <li class="not-mob">
                    <div class="flex flex--center flex--automargin">
                        @if($meta_app_settings['dcr_home_contact']['val'] ?? '')
                            <a href="{{ $meta_app_settings['dcr_home_contact']['val'] }}" target="_blank">
                                <button class="btn btn-success blue-gradient" :style="envelopeSpecStyle">
                                    <i class="far fa-envelope"></i>
                                </button>
                            </a>
                        @endif

                        @if($route_group == 'homepage' || $route_group == 'getstarted')
                            @if($meta_app_settings['dcr_home_contact']['val'] ?? '')
                                <a href="{{ $meta_app_settings['dcr_home_demo']['val'] }}" target="_blank">
                                    <button style="padding: 3px 7px;" class="btn btn-success blue-gradient" :style="$root.themeButtonStyle">Request a Demo</button>
                                </a>
                            @endif
                        @endif

                        @if($route_group != 'getstarted')
                            <a target="_blank" href="{{ route('getstarted') }}">
                                <button style="padding: 4px 7px;"
                                        class="btn btn-success blue-gradient"
                                        :style="$root.themeButtonStyle"
                                        v-html="$root.labels['btn.get_started']"
                                ></button>
                            </a>
                        @endif
                    </div>
                </li>

                <li class="dropdown not-mob">
                    @if (Auth::guard()->user())
                        <a href="#" class="dropdown-toggle avatar" data-toggle="dropdown">
                            <img src="{{ auth()->user()->present()->avatar }}" class="img-circle avatar">
                            {{ Auth::user()->present()->name }}
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('profile') }}" target="_blank">
                                    <i class="fas fa-user text-muted mr-2"></i>
                                    @lang('app.my_profile')
                                </a>
                            </li>
                            @if (config('session.driver') == 'database')
                                <li>
                                    <a href="{{ route('profile.sessions') }}">
                                        <i class="fa fa-list"></i>
                                        @lang('app.active_sessions')
                                    </a>
                                </li>
                            @endif
                            @if (!in_array($route_group,['payment_processing']))
                                <li>
                                    <a href="javascript:void(0)" v-on:click="openUserPopup()">
                                        <i class="glyphicon glyphicon-tasks"></i>
                                        <span>Subscription</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" v-on:click="show_resource_popup = true">
                                        <i class="glyphicon glyphicon-transfer"></i>
                                        <span>Settings</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" v-on:click="show_invite = !show_invite">
                                        <i class="glyphicon glyphicon-share-alt"></i>
                                        <span>Tell Friends</span>
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->subdomain)
                            <li>
                                @if ($is_app_route)
                                    <a href="{{ route('data') }}" target="_blank">
                                        <i class="fas fa-tablet-alt"></i>
                                        <span>Data</span>
                                    </a>
                                @else
                                    <a href="{{ route('apps') }}" target="_blank">
                                        <i class="fas fa-tablet-alt"></i>
                                        <span>Apps</span>
                                    </a>
                                @endif
                            </li>
                            @endif
                            <li>
                                <a href="{{ route('auth.logout') }}">
                                    <i class="fa fa-sign-out"></i>
                                    @lang('app.logout')
                                </a>
                            </li>
                        </ul>
                    @else
                        <a style="margin: 10px 0 8px 0;" class="dropdown-item" href="javascript:void(0)" v-on:click="$emit('show_login')">
                            <i class="fas fa-sign-in-alt text-muted mr-2"></i>
                            @lang('app.login')
                        </a>
                    @endif
                </li>
            </ul>
        </div>
        @if (auth()->user() && !in_array($route_group,['payment_processing']))
        <user-plans v-if="$root.settingsMeta.is_loaded"
                    v-show="show_user_popup"
                    :is_vis="show_user_popup"
                    :stripe_key="'{{ $stripe_key }}'"
                    v-on:popup-close="show_user_popup = false"
        ></user-plans>
        <resources-popup v-if="$root.settingsMeta.is_loaded"
                    v-show="show_resource_popup"
                    :is_vis="show_resource_popup"
                    v-on:popup-close="show_resource_popup = false"
        ></resources-popup>
        <invite-module v-if="$root.settingsMeta.is_loaded"
                    v-show="show_invite"
                    :is_vis="show_invite"
                    :app_url="'{{ $clear_url }}'"
                    v-on:popup-close="show_invite = false"
        ></invite-module>
        @endif


        <div v-show="isRequestDemo" class="modal-form-background" @click.self="isRequestDemo = false"></div>
        <div v-show="isRequestDemo" class="contact-container">
            <form @submit.prevent="sendForm()">
                <div class="form-group">
                    <input class="form-control" type="text" v-model="email" placeholder="Email">
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" v-model="subject" placeholder="Subject">
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" v-model="company" placeholder="Company">
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" v-model="pref_time" placeholder="Preferred time">
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
</navbar-data>

