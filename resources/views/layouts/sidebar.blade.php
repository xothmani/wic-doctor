<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-{{setting('theme_contrast')}}-{{setting('theme_color')}} shadow">
    <!-- Brand Logo -->
    <a href="{{url('dashboard')}}" class="brand-link border-bottom-0 {{setting('logo_bg_color','bg-white')}}">
        <img src="{{$app_logo ?? ''}}" alt="{{setting('app_name')}}" class="brand-image">
<!--         <span class="brand-text font-weight-light">{{setting('app_name')}}</span>
 -->    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu', ['icons' => true])
                <li class="nav-item mb-0">
            @unless(auth()->user()->hasRole('admin'))
            <a class="nav-link {{ Request::is('users.profile') ? 'active' : '' }}" href="{!! route('users.profile') !!}">
    <i class="nav-icon fas fa-user"></i> <!-- Icône de profil -->
    <p>{{ trans('lang.my_profile') }}</p>

</a>

            @endunless


</li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->

        <!-- Footer for extra links (Profile, Photos Cabinet) -->
        <div class="sidebar-footer mt-auto">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat">
            

            </ul>
        </div>
    </div>
    <!-- /.sidebar -->
</aside>
<style>
    /* Ajoutez ceci à votre fichier CSS */
.main-sidebar .sidebar-footer {
    position: absolute;
    bottom: 0;
}

</style>