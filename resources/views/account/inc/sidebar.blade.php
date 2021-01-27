<aside>
	<div class="inner-box">
		<div class="user-panel-sidebar">
            
            @if (isset($user))
                <div class="collapse-box">
                    <h5 class="collapse-title no-border">
                        {{ t('My Account') }}&nbsp;
                        <a href="#MyClassified" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                    </h5>
                    <div class="panel-collapse collapse show" id="MyClassified">
                        <ul class="acc-list">
                            <li>
                                <a {!! ($pagePath=='') ? 'class="active"' : '' !!} href="{{ lurl('account') }}">
                                    <i class="icon-home"></i> {{ t('Personal Home') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /.collapse-box  -->
                
                @if (!empty($user->user_type_id) and $user->user_type_id != 0)
                    <div class="collapse-box">
                        <h5 class="collapse-title">
                            {{ t('My Ads') }}&nbsp;
                            <a href="#MyAds" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                        </h5>
                        <div class="panel-collapse collapse show" id="MyAds">
                            <ul class="acc-list">
                                <!-- COMPANY -->
                                @if (in_array($user->user_type_id, [1]))
                                    <li>
                                        <a{!! ($pagePath=='companies') ? ' class="active"' : '' !!} href="{{ lurl('account/companies') }}">
                                        <i class="icon-town-hall"></i> {{ t('My companies') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countCompanies) ? \App\Helpers\Number::short($countCompanies) : 0 }}
										</span>
                                        </a>
                                    </li>
									<li>
										<a{!! ($pagePath=='my-posts') ? ' class="active"' : '' !!} href="{{ lurl('account/my-posts') }}">
										<i class="icon-docs"></i> {{ t('My ads') }}&nbsp;
										<span class="badge badge-pill">
											{{ isset($countMyPosts) ? \App\Helpers\Number::short($countMyPosts) : 0 }}
										</span>
										</a>
									</li>
                                    <li>
                                        <a{!! ($pagePath=='pending-approval') ? ' class="active"' : '' !!} href="{{ lurl('account/pending-approval') }}">
                                        <i class="icon-hourglass"></i> {{ t('Pending approval') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countPendingPosts) ? \App\Helpers\Number::short($countPendingPosts) : 0 }}
										</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='archived') ? ' class="active"' : '' !!} href="{{ lurl('account/archived') }}">
                                        <i class="icon-folder-close"></i> {{ t('Archived ads') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countArchivedPosts) ? \App\Helpers\Number::short($countArchivedPosts) : 0 }}
										</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='conversations') ? ' class="active"' : '' !!} href="{{ lurl('account/conversations') }}">
                                        <i class="icon-mail-1"></i> {{ t('Conversations') }}&nbsp;
										<span class="badge badge-pill">
												{{ isset($countConversations) ? \App\Helpers\Number::short($countConversations) : 0 }}
											</span>&nbsp;
										<span class="badge badge-pill badge-important count-conversations-with-new-messages">0</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='transactions') ? ' class="active"' : '' !!} href="{{ lurl('account/transactions') }}">
                                        <i class="icon-money"></i> {{ t('Transactions') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countTransactions) ? \App\Helpers\Number::short($countTransactions) : 0 }}
										</span>
                                        </a>
                                    </li>
                                @endif
								<!-- CANDIDATE -->
                                @if (in_array($user->user_type_id, [2]))
									<li>
										<a{!! ($pagePath=='resumes') ? ' class="active"' : '' !!} href="{{ lurl('account/resumes') }}">
										<i class="icon-attach"></i> {{ t('My resumes') }}&nbsp;
										<span class="badge badge-pill">
											{{ isset($countResumes) ? \App\Helpers\Number::short($countResumes) : 0 }}
										</span>
										</a>
									</li>
                                    <li>
                                        <a{!! ($pagePath=='favourite') ? ' class="active"' : '' !!} href="{{ lurl('account/favourite') }}">
                                        <i class="icon-heart"></i> {{ t('Favourite jobs') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countFavouritePosts) ? \App\Helpers\Number::short($countFavouritePosts) : 0 }}
										</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='saved-search') ? ' class="active"' : '' !!} href="{{ lurl('account/saved-search') }}">
                                        <i class="icon-star-circled"></i> {{ t('Saved searches') }}&nbsp;
                                        <span class="badge badge-pill">
											{{ isset($countSavedSearch) ? \App\Helpers\Number::short($countSavedSearch) : 0 }}
										</span>
                                        </a>
                                    </li>
									<li>
										<a{!! ($pagePath=='conversations') ? ' class="active"' : '' !!} href="{{ lurl('account/conversations') }}">
										<i class="icon-mail-1"></i> {{ t('Conversations') }}&nbsp;
										<span class="badge badge-pill">
											{{ isset($countConversations) ? \App\Helpers\Number::short($countConversations) : 0 }}
										</span>&nbsp;
										<span class="badge badge-important count-conversations-with-new-messages">0</span>
										</a>
									</li>
                                @endif
								@if (config('plugins.apijc.installed'))
									<li>
										<a{!! ($pagePath=='api-dashboard') ? ' class="active"' : '' !!} href="{{ lurl('account/api-dashboard') }}">
										<i class="icon-cog"></i> {{ trans('api::messages.Clients & Applications') }}&nbsp;
										</a>
									</li>
								@endif
                            </ul>
                        </div>
                    </div>
                    <!-- /.collapse-box  -->
                
                    <div class="collapse-box">
                        <h5 class="collapse-title">
                            {{ t('Terminate Account') }}&nbsp;
                            <a href="#TerminateAccount" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                        </h5>
                        <div class="panel-collapse collapse show" id="TerminateAccount">
                            <ul class="acc-list">
                                <li>
                                    <a {!! ($pagePath=='close') ? 'class="active"' : '' !!} href="{{ lurl('account/close') }}">
                                        <i class="icon-cancel-circled "></i> {{ t('Close account') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.collapse-box  -->
                @endif
            @endif

		</div>
	</div>
	<!-- /.inner-box  -->
</aside>