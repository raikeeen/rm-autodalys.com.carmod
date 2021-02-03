</section>

      <footer id="footer" class="footer-container">
        {block name="footer"}
          {include file="_partials/footer.tpl"}
        {/block}
        {if isset($LEO_PANELTOOL) && $LEO_PANELTOOL}
            {include file="$tpl_dir./modules/appagebuilder/views/templates/front/info/paneltool.tpl"}
        {/if}
        {if isset($LEO_BACKTOP) && $LEO_BACKTOP}
            <div id="back-top"><a href="#" class="fa fa-angle-double-up"></a></div>
        {/if}
      </footer>

    </main>

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}

    {block name='hook_before_body_closing_tag'}
      {hook h='displayBeforeBodyClosingTag'}
    {/block}
  </body>

</html>