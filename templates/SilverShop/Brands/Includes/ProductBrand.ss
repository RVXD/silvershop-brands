<% if $Brand %>
    <div class="product-brand">
        <% with $Brand %>
            <% if $Link %><a class="product-brand__link" href="$Link"><% end_if %>
                <% if $Logo %><img class="product-brand__logo" src="$Logo.Fill(60,60).URL" alt="$Title.ATT"><% end_if %>
                <span class="product-brand__name">$Title</span>
            <% if $Link %></a><% end_if %>
        <% end_with %>
    </div>
<% end_if %>
