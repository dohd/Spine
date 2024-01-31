<!-- BEGIN: Header-->
<nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-static-top navbar-dark bg-gradient-x-grey-blue navbar-border navbar-brand-center">
    <div class="navbar-wrapper">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
                <li class="nav-item"><a class="navbar-brand" href="{{route('biller.dashboard')}}"><img class="brand-logo" alt="Brand Logo" src="{{ Storage::disk('public')->url('app/public/img/company/theme/' . config('core.theme_logo')) }}">
                    </a></li>
                <li class="nav-item d-md-none"><a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="fa fa-ellipsis-v"></i></a></li>
            </ul>
        </div>
        <div class="navbar-container content">
            <div class="collapse navbar-collapse" id="navbar-mobile">
                <ul class="nav navbar-nav mr-auto float-left">
                    <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu"></i></a></li>
                    @permission('business_settings')
                        <li class="dropdown nav-item mega-dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown">{{trans('business.business_admin')}}</a>
                            <ul class="mega-dropdown-menu dropdown-menu row">
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase mb-1"><i class="fa fa-building-o"></i> {{trans('business.general_preference')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.business.settings')}}"><i class="ft-feather"></i>{{trans('business.company_settings')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.localization')}}"><i class="fa fa-globe"></i> {{trans('business.business_localization')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.transactioncategories.index')}}"><i class="ft-align-center"></i> {{trans('transactioncategories.transactioncategories')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.status')}}"><i class="fa fa-flag-o"></i> {{trans('meta.default_status')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{ route('biller.ticket_categories.index') }}"><i class="fa fa-sun-o"></i> Ticket Categories
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-random"></i> {{trans('business.billing_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.billing_preference')}}"><i class="fa fa-files-o"></i> {{trans('business.billing_settings_preference')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.additionals.index')}}"><i class="fa fa-floppy-o"></i> {{trans('business.tax_discount_management')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.prefixes.index')}}"><i class="fa fa-bookmark-o"></i> {{trans('business.prefix_management')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.terms.index')}}"><i class="fa fa-gavel"></i> {{trans('business.terms_management')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.pos_preference')}}"><i class="fa fa-shopping-cart"></i> {{trans('pos.preference')}}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-money"></i> {{trans('business.payment_account_settings')}}
                                    </h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.payment_preference')}}"><i class="fa fa-credit-card"></i> {{trans('business.payment_preferences')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.currencies.index')}}"><i class="fa fa-money"></i> {{trans('business.currency_management')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.banks.index')}}"><i class="ft-server"></i> {{trans('business.bank_accounts')}}
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item" href="{{route('biller.usergatewayentries.index')}}"><i class="fa fa-server"></i> {{trans('usergatewayentries.usergatewayentries')}}
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.accounts')}}"><i class="ft-compass"></i> {{trans('business.accounts_settings')}}
                                                    </a>
                                                </li>
                                                <li>&nbsp;</li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="ft-at-sign"></i> {{trans('business.communication_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.business.email_sms_settings')}}"><i class="ft-minimize-2"></i> {{trans('meta.email_sms_settings')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.notification_email')}}"><i class="ft-activity"></i> {{trans('meta.notification_email')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.templates.index')}}"><i class="fa fa-comments"></i> {{trans('templates.manage')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.currency_exchange')}}"><i class="fa fa-retweet"></i> {{trans('currencies.currency_exchange')}}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-random"></i> {{trans('business.miscellaneous_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.customfields.index')}}"><i class="ft-anchor"></i> {{trans('customfields.customfields')}}
                                                    </a>
                                                </li>
                                                <li><a class="dropdown-item" href="{{route('biller.productvariables.index')}}"><i class="ft-package"></i> {{trans('business.product_units')}}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-cogs"></i> {{trans('business.advanced_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.cron')}}"><i class="fa fa-terminal"></i> {{trans('meta.cron')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.web_update_wizard')}}"><i class="fa fa-magic"></i> {{trans('update.web_updater')}}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-asterisk"></i> {{trans('business.crm_hrm_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.crm_hrm_section')}}"><i class="fa fa-indent"></i> {{trans('meta.self_attendance')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.crm_hrm_section')}}"><i class="fa fa-key"></i> {{trans('meta.customer_login')}}
                                                    </a>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="col-md-3 col-sm-6">
                                    <h6 class="dropdown-menu-header text-uppercase"><i class="fa fa-camera-retro"></i> {{trans('business.visual_settings')}}</h6>
                                    <ul>
                                        <li class="menu-list">
                                            <ul>
                                                <li><a class="dropdown-item" href="{{route('biller.settings.theme')}}"><i class="fa fa-columns"></i> {{trans('meta.employee_panel_theme')}}
                                                    </a></li>
                                                <li><a class="dropdown-item" href="{{route('biller.about')}}"><i class="fa fa-info-circle"></i> {{trans('update.about_system')}}
                                                    </a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    @endauth
                    @permission('pos')
                        <li class="nav-item ">
                            <a href="{{route('biller.invoices.pos')}}" class="btn  btn-success round mt_6">
                                <i class="ficon ft-shopping-cart"></i>{{trans('pos.pos')}} </a>
                        </li>
                    @endauth
                    <li class="nav-item d-none d-md-block"><a class="nav-link nav-link-expand" href="#"><i class="ficon ft-maximize"></i></a></li>
                    <li class="dropdown">
                        <a href="#" class="nav-link " data-toggle="dropdown" role="button" aria-expanded="false">
                            <i class="ficon ft-toggle-left"></i> </a>
                        <ul class="dropdown-menu lang-menu" role="menu">
                            <li class="dropdown-item"><a href="{{route('direction',['ltr'])}}"><i class="ficon ft-layout"></i> {{trans('meta.ltr')}}</a></li>
                            <li class="dropdown-item"><a href="{{route('direction',['rtl'])}}"><i class="ficon ft-layout"></i> {{trans('meta.rtl')}}</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav float-right">
                    @if (config('locale.status') && count(config('locale.languages')) > 1)
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ trans('menus.language-picker.language') }}
                                <span class="caret"></span>
                            </a>
                            @include('includes.partials.lang_focus')
                        </li>
                    @endif
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="#" data-toggle="dropdown" onclick="loadNotifications()"><i class="ficon ft-bell"></i><span class="badge badge-pill badge-danger badge-up" id="n_count">{{ auth()->user()->unreadNotifications->count() }}</span></a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right" id="user_notifications">
                        </ul>
                    </li>
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="#" data-toggle="dropdown">@if(session('clock', false))
                            <i class="ficon ft-clock spinner"></i>
                            <span class="badge badge-pill badge-info badge-up">{{trans('general.on') }}</span>
                            @else
                            <i class="ficon ft-clock"></i>
                            <span class="badge badge-pill badge-danger badge-up"> {{trans('general.off') }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                            <li class="scrollable-container media-list">
                                <div class="media">
                                    <div class="media-body text-center">
                                        @if(!session('clock', false)) <a href="{{route('biller.clock')}}" class="btn btn-success"><i class="ficon ft-clock spinner"></i> {{trans('hrms.clock_in') }}</a>
                                        @else
                                        <a href="{{route('biller.clock')}}" class="btn btn-secondary"><i class="ficon ft-clock"></i> {{trans('hrms.clock_out') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="{{route('biller.messages')}}"><i class="ficon ft-mail"></i><span class="badge badge-pill badge-warning badge-up">{{Auth::user()->newThreadsCount()}}</span></a>
                    </li>
                    <li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown"><span class="avatar avatar-online"><img src="{{ Storage::disk('public')->url('app/public/img/users/' . @$logged_in_user->picture) }}" alt=""><i></i></span><span class="user-name">{{ $logged_in_user->name }}</span></a>
                        <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="{{ route('biller.profile') }}"><i class="ft-user"></i> {{ trans('navs.frontend.user.account')}}</a><a class="dropdown-item" href="{{route('biller.messages')}}"><i class="ft-mail"></i> My
                                Inbox</a><a class="dropdown-item" href="{{route('biller.todo')}}"><i class="ft-check-square"></i>
                                {{ trans('general.tasks')}}</a><a class="dropdown-item" href="{{route('biller.attendance')}}"><i class="ft-activity"></i>
                                {{ trans('hrms.attendance')}}</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('biller.logout', 'auth=1') }}"><i class="ft-power"></i> {{ trans('navs.general.logout') }}</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->
<!-- BEGIN: Main Menu-->
<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-light navbar-without-dd-arrow navbar-shadow menu-border" role="navigation" data-menu="menu-wrapper">
    <!-- Horizontal menu content-->
    <div class="navbar-container main-menu-content" data-menu="menu-container">
        <!-- include ../../../includes/mixins-->
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="dropdown nav-item">
                <a class="nav-link {{ (strpos(Route::currentRouteName(), 'biller.dashboard') === 0) ? 'active' : '' }}" href="{{route('biller.dashboard')}}"><i class="ft-home"></i><span>{{ trans('navs.frontend.dashboard') }}</span></a>
            </li>

            {{-- customer relation management module --}}
            @if(access()->allow('crm')) 
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-diamond"></i><span>{{trans('features.crm')}}</span></a>
                    <ul class="dropdown-menu">
                        {{-- customer --}}
                        @permission('manage-client')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-users"></i></i> {{ trans('labels.backend.customers.management') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.customers.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Customers
                                    </a>
                                </li>
                                @permission('create-client')
                                <li><a class="dropdown-item" href="{{ route('biller.customers.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> {{ trans('labels.backend.customers.create') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Client branch --}}
                        @permission('manage-branch')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-bandcamp" aria-hidden="true"></i> Branch Management</a>
                            
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.branches.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Branches
                                    </a>
                                </li>
                                @permission('create-branch')
                                <li><a class="dropdown-item" href="{{ route('biller.branches.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i>Create Branch
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Client group --}}
                        @permission('manage-clientgroup')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-grid"></i></i> {{ trans('labels.backend.customergroups.management') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.customergroups.index') }}" data-toggle="dropdown"><i class="ft-list"></i> {{ trans('labels.backend.customergroups.management') }}
                                    </a>
                                </li>
                                @permission('create-clientgroup')
                                <li><a class="dropdown-item" href="{{ route('biller.customergroups.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> {{ trans('labels.backend.customergroups.create') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Client Pricelist --}}
                        @permission('manage-pricelist')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Client Pricelist</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_products.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Pricelist 
                                    </a>
                                </li>
                                @permission('create-pricelist')
                                <li><a class="dropdown-item" href="{{ route('biller.client_products.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Pricelist
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        <hr>

                        {{-- Client Vendor Management --}}
                        @permission('manage-client')
                            <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-users" aria-hidden="true"></i> Client Users</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('biller.client_users.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Users </a></li>
                                    <li><a class="dropdown-item" href="{{ route('biller.client_users.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create User</a></li>
                                </ul>
                            </li>
                        @endauth
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-ship" aria-hidden="true"></i> Vendor Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendors.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Vendors </a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendors.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Vendor</a></li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-sun-o"></i> Ticket Tags</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tags.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Tags </a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tags.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Tag</a></li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-comments-o" aria-hidden="true"></i> Support Tickets</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tickets.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Support Tickets</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.client_vendor_tickets.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket</a></li>                                
                            </ul>
                        </li>
                    </ul>
                </li>
            @endif 

            {{-- sales module --}}
            @if(access()->allow('sale'))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-basket"></i><span>{{trans('features.sales')}}</span></a>
                    <ul class="dropdown-menu">
                        {{-- lead --}}
                         @permission('manage-lead')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-phone-outgoing"></i> Tickets</a>
                            <ul class="dropdown-menu">
                                 @permission('manage-lead') 
                                <li><a class="dropdown-item" href="{{ route('biller.leads.index')}}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Tickets</a></li>
                                @endauth 
                                @permission('create-lead') 
                                <li><a class="dropdown-item" href="{{ route('biller.leads.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket</a></li>
                                 @endauth 
                            </ul>
                        </li>
                             @endauth 
                        {{-- diagnosis job card --}}
                         @permission('manage-djc') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-tag"></i> Djc Report</a>
                            <ul class="dropdown-menu">
                                 @permission('manage-djc') 
                                <li><a class="dropdown-item" href="{{ route('biller.djcs.index')}}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Djc Report</a></li>
                                 @endauth 
                                 @permission('create-djc') 
                                <li><a class="dropdown-item" href="{{ route('biller.djcs.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Report</a></li>
                                 @endauth
                            </ul>
                        </li>
                         @endauth 
                        {{-- quote --}}
                         @permission('manage-quote') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> {{ trans('quotes.management') }}</a>
                            <ul class="dropdown-menu">
                                
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Quote  </a></li>
                            
                                 @permission('create-quote') 
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.quotes.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> {{ trans('labels.backend.quotes.create') }}</a>
                                    <a class="dropdown-item" href="{{ route('biller.quotes.create', 'doc_type=maintenance') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Maintenance Quote</a>
                                </li>  
                                 @endauth                           
                            </ul>
                        </li>
                         @endauth 
                        {{-- proforma-invoice --}}
                         @permission('manage-pi') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i>PI Management</a>
                            <ul class="dropdown-menu">
                                 @permission('manage-pi') 
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.index', 'page=pi') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage PI </a></li> 
                                 @endauth 
                                 @permission('create-pi') 
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.create', 'page=pi') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create PI</a></li>     
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.create', 'page=pi&doc_type=maintenance') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Maintenance PI</a></li>
                                @endauth 
                            </ul>
                        </li>
                         @endauth 
                        
                        {{-- verification --}}
                         @permission('manage-quote-verify') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Job Verification</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.quotes.get_verify_quote') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Verification</a></li>                                                            
                            </ul>                        
                        </li>
                         @endauth 
                        {{-- repair job card --}}
                         @permission('manage-rjc') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-tag"></i> Rjc Report</a>
                            <ul class="dropdown-menu">
                                 @permission('manage-rjc') 
                                <li><a class="dropdown-item" href="{{ route('biller.rjcs.index') }}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Rjc Report</a></li>
                                 @endauth 
                                 @permission('create-rjc') 
                                <li><a class="dropdown-item" href="{{ route('biller.rjcs.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Report</a></li>
                                 @endauth 
                            </ul>
                        </li>
                         @endauth 
                    </ul>
                </li>
            @endif

            {{-- maintenace project module --}}
            @if(access()->allow('maintenance-project'))
                <li class="dropdown nav-item" data-menu="dropdown">
                    <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="fa fa-industry"></i><span>Project Management</span></a>
                    <ul class="dropdown-menu">
                        {{-- project --}}
                        @permission('manage-project') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-calendar"></i> Projects</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.projects.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage {{ trans('projects.projects')}}</a>
                                </li>
                            </ul>
                        </li>
                        @endauth 

                        {{-- project tags --}}
                        @permission('manage-project') 
                            @if (!auth()->user()->customer_id)
                                <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-calendar"></i> Project Tags</a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('biller.miscs.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Tags</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('biller.miscs.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Tags
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                        @endauth 
                        
                        {{-- partial verification --}}
                        {{-- @permission('manage-quote-verify') 
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Partial Job Verification</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.verifications.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Verification</a></li>                                                            
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.verifications.quote_index') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Verification
                                    </a>
                                </li>
                            </ul>                        
                        </li>
                         @endauth  --}}
                        <hr>

                        @permission('manage-equipment-category')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-tag"></i> Equipment Category</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.equipmentcategories.index')}}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Categories
                                    </a>
                                </li>
                                @permission('create-equipment-category')
                                <li><a class="dropdown-item" href="{{ route('biller.equipmentcategories.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Category
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        @permission('manage-equipment')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-tag"></i>Maintenance Equipments</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.equipments.index')}}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Equipment
                                    </a>
                                </li>
                                @permission('create-equipment')
                                <li><a class="dropdown-item" href="{{ route('biller.equipments.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Equipment
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        
                        @permission('manage-pm-contract')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-file-text-o"></i>PM Contract Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.contracts.index')}}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage PM Contracts
                                    </a>
                                </li>
                                @permission('create-pm-contract')
                                <li><a class="dropdown-item" href="{{ route('biller.contracts.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create PM Contract
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('biller.contracts.create_add_equipment') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Add PM Equipment
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-schedule')
                        <li class="dropdown dropdown-submenu"><a class="dropdown-item" href="#" data-toggle="dropdown"> <i class="fa fa-calendar"></i> Schedule Management </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.taskschedules.index') }}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage Schedule 
                                    </a>                                
                                </li>
                                @permission('create-schedule')
                                <li><a class="dropdown-item" href="{{ route('biller.taskschedules.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Load Equipment
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-pm-report')
                        <li class="dropdown dropdown-submenu"><a class="dropdown-item" href="#" data-toggle="dropdown"> <i class="fa fa-wrench"></i> PM Report Management</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.contractservices.index') }}" data-toggle="dropdown"> <i class="fa fa-compass"></i> Manage PM Report 
                                    </a>                                
                                </li>
                                @permission('create-pm-report')
                                <li><a class="dropdown-item" href="{{ route('biller.contractservices.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create PM Report
                                    </a>
                                </li>
                                @endauth
                                <li><a class="dropdown-item" href="{{ route('biller.contractservices.serviced_equipment') }}" data-toggle="dropdown"> <i class="icon-tag"></i> Serviced Equipments
                                    </a>                                
                                </li>
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </li>
            @endif
             
            {{-- inventory module --}}
            @if(access()->allow('stock'))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="ft-layers"></i><span>Inventory</span></a>
                    <ul class="dropdown-menu">
                        {{-- Warehouse --}}
                        @permission('manage-warehouse')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-building-o"></i> Product Location
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.warehouses.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Location Management
                                    </a>
                                </li>
                                @permission('create-warehouse')
                                <li><a class="dropdown-item" href="{{ route('biller.warehouses.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Location
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Product Category --}}
                        @permission('manage-product-category')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-object-ungroup"></i> Product Categories
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.productcategories.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Categories Management
                                    </a>
                                </li>
                                @permission('create-product-category')
                                <li><a class="dropdown-item" href="{{ route('biller.productcategories.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('labels.backend.productcategories.create') }}
                                    </a>
                                </li>
                                 @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Product Management --}}
                        @permission('manage-product')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cube"></i> {{ trans('labels.backend.products.management') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.products.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> {{ trans('labels.backend.products.management') }}
                                    </a>
                                </li>
                                @permission('create-product')
                                <li><a class="dropdown-item" href="{{ route('biller.products.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('labels.backend.products.create') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Product Opening Stock --}}
                        @permission('manage-opening-stock')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-balance-scale"></i> Opening Stock</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.opening_stock.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage Opening Stock</a>
                                </li>
                                @permission('create-opening-stock')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.opening_stock.create')}}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Opening Stock</a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Goods Receive Note --}}
                        @permission('manage-grn')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-puzzle-piece"></i> Goods Receive Note</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.goodsreceivenote.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage GRN
                                    </a>
                                </li>
                                @permission('create-grn')
                                <li><a class="dropdown-item" href="{{ route('biller.goodsreceivenote.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create GRN
                                    </a>
                                </li>     
                                @endauth                       
                            </ul>
                        </li>
                        @endauth

                        {{-- stock issuance --}}
                        @permission('manage-issuance')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cubes" aria-hidden="true"></i> Project Stock Issuance</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.projectstock.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Project Stock </a></li>                                   
                                @permission('create-issuance')
                                <li><a class="dropdown-item" href="{{ route('biller.projectstock.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Stock Issuance </a></li> 
                                @endauth
                            </ul>
                        </li>  
                        @endauth   

                        {{-- Stock Transfer --}}
                        @permission('manage-stock-transfer')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-wind"></i> {{ trans('products.stock_transfer') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.stock_transfers.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Transfer Management
                                    </a>
                                </li>
                                @permission('create-stock-transfer')
                                <li><a class="dropdown-item" href="{{ route('biller.stock_transfers.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Transfer
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        {{-- Asset and Equipments  --}}
                        @permission('manage-asset-equipment')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-target"></i> Assets & Equipments
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.assetequipments.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Assets & Equipments Management
                                    </a>
                                </li>
                                @permission('create-asset-equipment')
                                <li><a class="dropdown-item" href="{{ route('biller.assetequipments.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Assets & Equipments
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </li>
            @endif
            
            {{-- procurement module --}}
            @if(access()->allow('finance'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="fa fa-tags"></i><span>Procurement</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-supplier')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-target"></i> Supplier Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.suppliers.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Suppliers
                                </a>
                            </li>
                            @permission('create-supplier')
                            <li><a class="dropdown-item" href="{{ route('biller.suppliers.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('labels.backend.suppliers.create') }}
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    {{-- Supplier Pricelist --}}
                    @permission('manage-pricelist')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Supplier Pricelist</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.pricelistsSupplier.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Pricelist 
                                </a>
                            </li>
                            @permission('create-pricelist')
                            <li><a class="dropdown-item" href="{{ route('biller.pricelistsSupplier.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Pricelist
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    {{-- Purchase Requisition Management --}}
                    @permission('manage-product')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-cube"></i> Requisition Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requests.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Requisition
                                </a>
                            </li>
                            @permission('create-product')
                            <li><a class="dropdown-item" href="{{ route('biller.purchase_requests.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Requisition
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    {{-- Manage Purchases --}}
                    @permission('manage-purchase')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="ft-file-text"></i> Purchase Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.purchases.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Direct Purchases
                                </a>
                            </li>
                            @permission('create-purchase')
                            <li><a class="dropdown-item" href="{{ route('biller.purchases.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Direct Purchase
                                </a>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.purchaseorders.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Purchase Orders
                                </a>
                            </li>
                            @permission('create-purchase')
                            <li><a class="dropdown-item" href="{{ route('biller.purchaseorders.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Purchase Order
                                </a>
                            </li>                            
                            @endauth                            
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-debit-note')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Debit Note</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.index') }}?is_debit=1" data-toggle="dropdown"><i class="ft-list"></i> Manage Debit Notes
                                </a>
                            </li>
                            @permission('create-debit-note')
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.create') }}?is_debit=1" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Debit Note
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
            @endif

            {{-- finance module --}}
            @if(access()->allow('finance'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-calculator"></i><span>{{trans('general.finance')}}</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-client-lpo')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-file-o"></i> Client LPO</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.lpo.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Client LPO</a></li>                           
                        </ul>
                    </li>
                    @endauth                    

                    @permission('manage-bill')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-usd"></i> Bills Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.utility-bills.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Bills</a></li>
                            @permission('create-bill')
                            <li><a class="dropdown-item" href="{{ route('biller.utility-bills.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i>Create Bill</a></li> 
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-invoice')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-usd"></i> Invoice Management</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.invoices.index') }}" data-toggle="dropdown"><i class="ft-file-text"></i> Manage Project Invoice
                                </a>
                            </li>
                            @permission('create-invoice')
                            <li><a class="dropdown-item" href="{{ route('biller.invoices.uninvoiced_quote') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Project Invoice
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('biller.standard_invoices.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Detached Invoice
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-credit-note')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Credit Note</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.index') }}" data-toggle="dropdown"><i class="ft-list"></i> {{ trans('orders.credit_notes_manage')}}
                                </a>
                            </li>
                            @permission('create-credit-note')
                            <li><a class="dropdown-item" href="{{ route('biller.creditnotes.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> {{ trans('orders.credit_notes_create') }}
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                
                    @permission('manage-withholding-cert')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-file"></i>Withhoding Certificate
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.withholdings.index') }}" data-toggle="dropdown"> <i class="ft-list"></i>Manage Withholding Certificates
                                </a>
                            </li>
                            @permission('create-withholding-cert')
                            <li><a class="dropdown-item" href="{{ route('biller.withholdings.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Withholding Certificate
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    
                    @permission('manage-journal')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-newspaper-o"></i> Manual Journal
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.journals.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Journal
                                </a>
                            </li>
                            @permission('create-journal')
                            <li><a class="dropdown-item" href="{{ route('biller.journals.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Journal
                                </a>
                            </li>
                            @endauth                            
                        </ul>
                    </li>
                    @endauth

                    {{-- Account --}}
                    @permission('manage-account')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-book"></i> Charts Of Accounts
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.accounts.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Ledger Accounts
                                </a>
                            </li>
                            @permission('create-account')
                            <li><a class="dropdown-item" href="{{ route('biller.accounts.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ledger Account
                                </a>
                            </li>
                            @endauth
                            <li><a class="dropdown-item" href="{{ route('biller.transactions.index') }}" data-toggle="dropdown"> <i class="fa fa-exchange"></i> Journal Entries
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Accounting Report --}}
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-file-text-o"></i> Accounting Report</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.trial_balance', 'v') }}"><i class="fa fa-balance-scale"></i> Trial Balance</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.balance_sheet', 'v') }}"><i class="fa fa-book"></i> {{ trans('accounts.balance_sheet') }}</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.profit_and_loss', 'v') }}"><i class="fa fa-money"></i> Profit & Loss</a></li>
                            
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.cashbook') }}"><i class="fa fa-book"></i>Cashbook Statement</a></li>
                        
                            <li><a class="dropdown-item " href="{{ route('biller.accounts.project_gross_profit') }}"><i class="fa fa-money"></i>Project Gross Profit</a></li>
                        </ul>
                    </li>

                    {{-- Tax Report --}}
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-balance-scale"></i> Tax Returns</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.tax_reports.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Tax Returns</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.tax_reports.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Tax Return</a></li>
                            <li><a class="dropdown-item " href="{{ route('biller.tax_reports.filed_report') }}"><i class="fa fa-book"></i> Filed Tax Returns</a></li>
                        </ul>
                    </li>

                    {{-- Tax PRN --}}
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-check-square-o"></i> Return Acknowledgement</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.tax_prns.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Return Acknowledgement</a></li>
                            <li><a class="dropdown-item" href="{{ route('biller.tax_prns.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Return Acknowledgement</a></li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
            @endif

            {{-- banking module --}}
            @if(access()->allow('finance'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="fa fa-bank"></i><span>Banking</span></a>
                <ul class="dropdown-menu">
                    @permission('manage-bill')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-credit-card" aria-hidden="true"></i> Bill Payment</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.billpayments.index') }}"><i class="fa fa-money"></i> Manage Payments</a></li>
                            @permission('create-bill')
                            <li><a class="dropdown-item" href="{{ route('biller.billpayments.create') }}"><i class="fa fa-plus-circle"></i> Make Payment</a></li>                           
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-invoice')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Invoice Payment</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.invoice_payments.index') }}"><i class="fa fa-money"></i> Manage Payments</a></li>
                            @permission('create-invoice')
                            <li><a class="dropdown-item" href="{{ route('biller.invoice_payments.create') }}"><i class="fa fa-plus-circle"></i> Receive Payment</a></li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    
                    @permission('manage-money-transfer')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-exchange"></i> Money Transfer
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.banktransfers.index') }}" data-toggle="dropdown"> <i class="ft-list"></i>Manage Transfer
                                </a>
                            </li>
                            @permission('create-money-transfer')
                            <li><a class="dropdown-item" href="{{ route('biller.banktransfers.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Transfer
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth

                    @permission('manage-account-charge')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Account Charges
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.charges.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Account Charges
                                </a>
                            </li>
                            @permission('create-account-charge')
                            <li><a class="dropdown-item" href="{{ route('biller.charges.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Account Charges
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </li>
                    @endauth
                    
                    @permission('manage-reconciliation')
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-handshake-o"></i>Reconciliation
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('biller.reconciliations.index') }}" data-toggle="dropdown"><i class="ft-list"></i>Manage Reconciliations
                                </a>
                            </li> 
                            @permission('create-reconciliation')
                            <li><a class="dropdown-item" href="{{ route('biller.reconciliations.create') }}" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> Create Reconciliation
                                </a>
                            </li>        
                            @endauth                                            
                        </ul>
                    </li>
                    @endauth
                </ul>
            </li>
            @endif

            {{-- human resource module --}}
            @if(access()->allow('hrm'))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-badge"></i><span>{{trans('features.hrm')}}</span></a>
                    <ul class="dropdown-menu">
                        @permission('manage-department')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-users"></i> {{ trans('hrms.management') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.hrms.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> {{ trans('hrms.employees') }}</a>
                                </li>
                                @permission('create-department')
                                <li><a class="dropdown-item" href="{{ route('biller.hrms.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('hrms.create') }}
                                    </a>
                                </li>
                                @endauth
                                <li><a class="dropdown-item" href="{{ route('biller.role.index') }}" data-toggle="dropdown"> <i class="ft-pocket"></i> {{ trans('hrms.roles') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('biller.departments.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> {{ trans('departments.departments') }}</a>
                        </li>
                        @endauth

                        @permission('manage-holiday')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fas fa-hotel"></i> Holiday Management</a>
                            <ul class="dropdown-menu">
                                @permission('manage-holiday')
                                <li><a class="dropdown-item" href="{{ route('biller.holiday_list.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Holiday
                                    </a>
                                </li>
                                @endauth
                                @permission('create-holiday')
                                <li><a class="dropdown-item" href="{{ route('biller.holiday_list.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Holiday
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-leave')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fas fa-hotel"></i> Leave Category</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.leave_category.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Category
                                    </a>
                                </li>
                                @permission('create-leave')
                                <li><a class="dropdown-item" href="{{ route('biller.leave_category.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Category
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                    
                    
                        @permission('manage-leave')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fas fa-hotel"></i> Leave Application</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.leave.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Leave Application
                                    </a>
                                </li>
                                @permission('create-leave')
                                <li><a class="dropdown-item" href="{{ route('biller.leave.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Leave
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                        
                        @permission('manage-attendance')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa ft-activity"></i> {{ trans('hrms.attendance') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.attendances.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage {{ trans('attendances') }}
                                    </a>
                                </li>
                                @permission('create-attendance')
                                <li><a class="dropdown-item" href="{{ route('biller.attendances.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('hrms.attendance_add') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-loan')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-briefcase"></i>Loan Management
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.loans.index') }}" data-toggle="dropdown"> <i class="ft-list"></i>Manage Loans
                                    </a>
                                </li>   
                                @permission('create-loan')
                                <li><a class="dropdown-item" href="{{ route('biller.loans.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Loan
                                    </a>
                                </li>         
                                @endauth                
                                <li><a class="dropdown-item" href="{{ route('biller.loans.pay_loans') }}" data-toggle="dropdown"> <i class="fa fa-money"></i> Pay Loans
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-advance-payment')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> Advance Payment</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.advance_payments.index') }}" data-toggle="dropdown"> <i class="ft-list"></i> Manage Payments</a>
                                </li>
                                @permission('create-advance-payment')
                                <li>
                                    <a class="dropdown-item" href="{{ route('biller.advance_payments.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Payment </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-payroll')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-money"></i> {{ trans('hrms.payroll') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.hrms.index') }}?rel_type=3" data-toggle="dropdown"> <i class="ft-list"></i> {{ trans('hrms.payroll') }}
                                    </a>
                                </li>
                                @permission('create-payroll')
                                <li><a class="dropdown-item" href="{{ route('biller.hrms.payroll') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> {{ trans('hrms.payroll_entry') }}
                                    </a>
                                </li>
                                @endauth
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </li>   
            @endif
            
            {{-- miscellaneous module --}}
            @if(access()->allowMultiple(['manage-note', 'manage-event', 'manage-project', 'manage-invoice']))
                <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-star"></i><span>{{trans('features.misc')}}</span></a>
                    <ul class="dropdown-menu">
                        @permission('manage-note')
                        <li><a class="dropdown-item" href="{{ route('biller.notes.index')? '#' : '#' }}" data-toggle="dropdown"><i class="icon-note"></i> {{trans('general.notes')}}</a>
                        </li>
                        @endauth
                        @permission('manage-event')
                        <li><a class="dropdown-item" href="{{route('biller.events.index')}}" data-toggle="dropdown"><i class="icon-calendar"></i> {{trans('features.calendar')}}</a>
                        </li>
                        @endauth

                        @permission('manage-project')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-tag"></i> IRD Jobcard</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ '#' }}" data-toggle="dropdown"> <i class="fa fa-compass"></i> IRD Report</a></li>
                                <li><a class="dropdown-item" href="{{ '#' }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create IRD Report</a></li>
                            </ul>
                        </li>
                        @endauth

                        @permission('manage-invoice')
                        <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-umbrella"></i> {{ trans('invoices.subscriptions') }}</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('biller.invoices.index')? '#' : '#' }}?md=sub" data-toggle="dropdown"><i class="ft-file-text"></i> {{ trans('invoices.subscriptions')}}
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('biller.invoices.create')? '#' : '#' }}?sub=true" data-toggle="dropdown"><i class="fa fa-plus-circle"></i> {{ trans('invoices.create_subscription') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </li>
            @endif

            {{-- data & reports module --}}
            @permission('reports-statements')
            <li class="dropdown mega-dropdown nav-item" data-menu="megamenu"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="icon-pie-chart"></i><span>{{trans('features.reports')}}</span></a>
                <ul class="mega-dropdown-menu dropdown-menu row">
                    {{-- statements --}}
                    <li class="col-md-3" data-mega-col="col-md-3">
                        <ul class="drilldown-menu">
                            <li class="menu-list">
                                <ul class="mega-menu-sub">
                                    <li class="nav-item text-bold-600 ml-1 text-info p-1">{{trans('meta.statements')}}
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-book"></i>{{trans('meta.finance_account_statement')}}
                                        </a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['account'])}}"><i class="icon-doc"></i> {{trans('meta.finance_account_statement')}}
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['income'])}}"><i class="icon-doc"></i> {{trans('meta.income_statement')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['expense'])}}"><i class="icon-doc"></i> {{trans('meta.expense_statement')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['pos_statement'])}}"><i class="icon-doc"></i> {{trans('meta.pos_statement')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-smile-o"></i>{{trans('customers.customer')}}</a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['customer'])}}" data-toggle="dropdown">{{trans('meta.customer_statements')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_customer_statement'])}}" data-toggle="dropdown">{{trans('meta.product_customer_statement')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-truck"></i>{{trans('suppliers.supplier')}}</a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['supplier'])}}" data-toggle="dropdown">{{trans('meta.supplier_statements')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_supplier_statement'])}}" data-toggle="dropdown">{{trans('meta.product_supplier_statement')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="icon-doc"></i>{{trans('meta.tax_statements')}}</a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['tax'])}}" data-toggle="dropdown">{{trans('meta.tax_statements')}} {{trans('meta.sales')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['tax'])}}?s=purchase" data-toggle="dropdown">{{trans('meta.tax_statements')}} {{trans('meta.purchase')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-th"></i>{{trans('meta.product_statement')}}</a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_statement'])}}" data-toggle="dropdown">{{trans('meta.product_statement')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_category_statement'])}}" data-toggle="dropdown">{{trans('meta.product_category_statement')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['product_warehouse_statement'])}}" data-toggle="dropdown">{{trans('meta.product_warehouse_statement')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="fa fa-road"></i>{{trans('products.stock_transfer')}}</a>
                                        <ul class="mega-menu-sub">
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['stock_transfer'])}}" data-toggle="dropdown">{{trans('meta.stock_transfer_statement_warehouse')}}</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{route('biller.reports.statements',['stock_transfer_product'])}}" data-toggle="dropdown">{{trans('meta.stock_transfer_statement_product')}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    {{-- grpahical reports --}}
                    <li class="col-md-3" data-mega-col="col-md-3">
                        <ul class="drilldown-menu">
                            <li class="menu-list">
                                <ul class="mega-menu-sub">
                                    <li class="nav-item text-bold-600 ml-1 text-info p-1">{{trans('meta.graphical_reports')}}
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.charts',['customer'])}}"><i class="fa fa-bar-chart"></i> {{trans('meta.customer_graphical_overview')}}
                                        </a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.charts',['supplier'])}}"><i class="fa fa-sun-o"></i> {{trans('meta.supplier_graphical_overview')}}
                                        </a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.charts',['product'])}}"><i class="ft-trending-up"></i> {{trans('meta.product_graphical_overview')}}
                                        </a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.charts',['income_vs_expenses'])}}"><i class="icon-pie-chart"></i> {{trans('meta.income_vs_expenses_overview')}}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    {{-- summary reports --}}
                    <li class="col-md-3" data-mega-col="col-md-3">
                        <ul class="drilldown-menu">
                            <li class="menu-list">
                                <ul class="mega-menu-sub">
                                    <li class="nav-item text-bold-600 ml-1 text-info p-1">{{trans('meta.summary_reports')}}
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.summary',['income'])}}"><i class="ft-check-circle"></i> {{trans('meta.income_summary')}}</a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.summary',['expense'])}}"><i class="fa fa fa-bullhorn"></i> {{trans('meta.expense_summary')}}</a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.summary',['sale'])}}"><i class="ft-aperture"></i> {{trans('meta.sale_summary')}}</a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.summary',['purchase'])}}"><i class="ft-disc"></i> {{trans('meta.purchase_summary')}}</a>
                                    </li>
                                    <li data-menu=""><a class="dropdown-item" href="{{route('biller.reports.summary',['products'])}}"><i class="ft-layers"></i> {{trans('meta.products_summary')}}</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    {{-- import data --}}
                    <li class="col-md-3" data-mega-col="col-md-3">
                        <ul class="drilldown-menu">
                            <li class="menu-list">
                                <ul class="mega-menu-sub">
                                    <li class="nav-item text-bold-600 ml-1 text-info p-1">{{trans('import.import')}}</li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['customer'])}}">
                                            <i class="fa fa-file-excel-o"></i> Customers
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['supplier'])}}">
                                            <i class="fa fa-file-excel-o"></i> Suppliers
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['products'])}}">
                                            <i class="fa fa-file-excel-o"></i> Products
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['accounts'])}}">
                                            <i class="fa fa-file-excel-o"></i> Accounts
                                        </a>
                                    </li>
                                
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['equipments'])}}">
                                            <i class="fa fa-file-excel-o"></i> Equipments
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['client_pricelist'])}}">
                                            <i class="fa fa-file-excel-o"></i> Client Pricelist
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{route('biller.import.general',['supplier_pricelist'])}}">
                                            <i class="fa fa-file-excel-o"></i> Supplier Pricelist
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            @endauth

            {{-- client-area module --}}
            @php
                $user = auth()->user();
                $app_owner = $user->ins == 1 && !$user->customer_id && !$user->supplier_id && !$user->client_vendor_id;
            @endphp
            @if($app_owner)
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="fa fa-anchor"></i><span>Client Area</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-check-square-o" aria-hidden="true"></i> Account Services</a>
                        <ul class="dropdown-menu">
                            @permission('manage-note')
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_services.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Account Services</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_services.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Account Service</a></li>
                            @endauth
                        </ul>
                    </li>
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-university"></i> Business Accounts</a>
                        <ul class="dropdown-menu">
                            @permission('manage-note')
                                <li><a class="dropdown-item" href="{{ route('biller.tenants.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Business Accounts</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenants.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Business Account</a></li>
                            @endauth
                        </ul>
                    </li>
                    
                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-usd" aria-hidden="true"></i> Invoices & Deposits</a>
                        <ul class="dropdown-menu">
                            @permission('manage-note')
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_invoices.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Invoices</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_deposits.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Deposits</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.mpesa_deposits.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage M-PESA Deposits</a></li>
                            @endauth
                        </ul>
                    </li>

                    <li class="dropdown dropdown-submenu" data-menu="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#" data-toggle="dropdown"><i class="fa fa-comments-o" aria-hidden="true"></i> Support Tickets</a>
                        <ul class="dropdown-menu">
                            @permission('manage-note')
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_tickets.index') }}" data-toggle="dropdown"><i class="ft-list"></i> Manage Support Tickets</a></li>
                                <li><a class="dropdown-item" href="{{ route('biller.tenant_tickets.create') }}" data-toggle="dropdown"> <i class="fa fa-plus-circle"></i> Create Ticket</a></li>
                            @endauth
                        </ul>
                    </li>
                </ul>
            </li>        
            @endif
        </ul>
    </div>
</div>
