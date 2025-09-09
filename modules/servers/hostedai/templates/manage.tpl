{assign var=unique_id value=10|mt_rand:20}
<link href="{$assets}/css/style.css?v={$unique_id}" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/validator/13.7.0/validator.min.js"></script>

<script src="{$assets}/js/custom.js?v={$unique_id}"></script>
<div class="container">

    <div class="panel panel-primary">
        <div class="panel-heading"><p>Overview</p> <a href="{if strpos($loginURL, 'http') !== 0}https://{/if}{$loginURL}" target="_blank" class="btn btn-primary">Login</a> </div>
        <div class="panel-body overview-main">
            <div class="row">
                {foreach from=$resourcesData key=key item=item}
                    <div class="col-lg-6 mt-2">
                        <div class="overview-card">
                            <div class="overview-card-header">
                                <img src="{$assets}/images/{$key}.svg" alt="{$key}">
                                <h3>{$key|upper|replace:'_':' '}</h3>
                            </div>
                            <div class="overview-card-detail">
                                <p>{$item->used} <b>{if $key == 'cores'} {$LANG['cores']} {elseif $key == 'gpus'} {$LANG['no_of_cards']} {else} {$LANG['storage_GB']} {/if}</b> {if $item->available == -1} {$LANG['infinity']} {else} ({$item->percent}%) {/if}</p>
                                <p>{if $item->available == -1} {$LANG['unlimited']} {else} {$item->available}{if $key == 'cores'} {$LANG['cores']} {elseif $key == 'gpus'} {$LANG['no_of_cards']} {else} {$LANG['storage_GB']} {/if} {/if}</p>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" aria-valuenow="{$item->percent}" aria-valuemin="{$item->percent}" aria-valuemax="{$item->percent}" style="width:{$item->percent}%">{$item->percent}%</div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>

    <div class="panel panel-success">
        <div class="panel-heading">{$LANG['team_heading']}</div>
        <div class="panel-body">
            <table class="table table-bordered">
                <thead class="members-list-head">
                    <tr>
                        <th scope="col">{$LANG['email']}</th>
                        <th scope="col">{$LANG['role']}</th>
                        <th scope="col">{$LANG['status']}</th>
                    </tr>
                </thead>
                
                <tbody>

                    {foreach from=$teammembers key=key item=member}
                        <tr>
                            <td>{$member->user->email}</td>
                            <td>{$member->role->label}</td>
                            <td>{$member->status|ucfirst}</td>
                        </tr>
                    {/foreach}
                   
                </tbody>
            </table>

        </div>
    </div>
</div>
