<div class="brand-detail">
    <% with $Brand %>
        <% if $Banner %><img class="brand__banner" src="$Banner.ScaleWidth(1200).URL" alt="$Title.ATT"><% end_if %>
        <h1 class="brand__heading">
            <% if $Logo %><img class="brand__logo" src="$Logo.Fill(80,80).URL" alt="$Title.ATT"><% end_if %>
            $Title
        </h1>
        <% if $Content %>$Content<% else_if $Description %>$Description<% end_if %>
        <% if $Website %>
            <p class="brand__website">
                <a href="$Website.ATT" rel="nofollow noopener" target="_blank"><%t SilverShop\\Brands\\BrandsPage.VisitWebsite 'Visit website' %></a>
            </p>
        <% end_if %>
    <% end_with %>

    <% if $Products.Count %>
        <ul class="products brand__products">
            <% loop $Products %>
                <li class="product"><a href="$Link">$Title</a></li>
            <% end_loop %>
        </ul>
        <% if $Products.MoreThanOnePage %>
            <nav class="pagination">
                <% if $Products.NotFirstPage %><a class="prev" href="$Products.PrevLink">&laquo;</a><% end_if %>
                <% loop $Products.Pages %>
                    <% if $CurrentBool %><span class="current">$PageNum</span><% else %><a href="$Link">$PageNum</a><% end_if %>
                <% end_loop %>
                <% if $Products.NotLastPage %><a class="next" href="$Products.NextLink">&raquo;</a><% end_if %>
            </nav>
        <% end_if %>
    <% else %>
        <p class="brand__no-products"><%t SilverShop\\Brands\\BrandsPage.NoProducts 'No products in this brand yet.' %></p>
    <% end_if %>
</div>
