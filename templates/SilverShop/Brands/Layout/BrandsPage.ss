<div class="brands-page">
    <h1>$Title</h1>

    <% if $Content %>$Content<% end_if %>

    <% if $FeaturedBrands.Count %>
        <ul class="brands-list brands-list--featured">
            <% loop $FeaturedBrands %>
                <li class="brand">
                    <a href="$Link">
                        <% if $Logo %><img class="brand__logo" src="$Logo.Fill(120,120).URL" alt="$Title.ATT" loading="lazy"><% end_if %>
                        <span class="brand__name">$Title</span>
                    </a>
                </li>
            <% end_loop %>
        </ul>
    <% end_if %>

    <ul class="brands-list">
        <% loop $Brands %>
            <li class="brand">
                <a href="$Link">
                    <% if $Logo %><img class="brand__logo" src="$Logo.Fill(120,120).URL" alt="$Title.ATT" loading="lazy"><% end_if %>
                    <span class="brand__name">$Title</span>
                </a>
            </li>
        <% end_loop %>
    </ul>
</div>
