<div class="z-formrow">
    <label for="advancedpolls_pollid">{gt text="Individual selection" domain="module_advancedpolls"}</label>
    {html_options id="advancedpolls_pollid" name="pollid" options=$items selected=$blockvars.pollid}
    <em class="z-sub z-formnote">{gt text="The selection method 'Latest' and 'Random' override this individual poll selection"}</em>
</div>
<div class="z-formrow">
    <label for="advancedpolls_polluse">{gt text="Poll to use" domain="module_advancedpolls"}</label>
    {html_options id="advancedpolls_polluse" name="polluse" options=$pollusevalues selected=$blockvars.polluse}
</div>
<div class="z-formrow">
    <label for="advancedpolls_ajaxvoting">{gt text="Use ajax for voting form" domain="module_advancedpolls"}</label>
    <input id="advancedpolls_ajaxvoting" type="checkbox" name="ajaxvoting" value="1"{if $blockvars.ajaxvoting eq 1} checked="checked"{/if} />
</div>
<div class="z-formrow">
    <label for="advancedpolls_pollopenclosebaseddisplay">{gt text="Display block based on open/close dates" domain="module_advancedpolls"}</label>
    {html_options id="advancedpolls_pollopenclosebaseddisplay" name="pollopenclosebaseddisplay" options=$yesno selected=$blockvars.pollopenclosebaseddisplay}
</div>
<div class="z-formrow">
    <label for="advancedpolls_polldisplayresults">{gt text="Display results page after vote" domain="module_advancedpolls"}</label>
    {html_options id="advancedpolls_polldisplayresults" name="polldisplayresults" options=$yesno selected=$blockvars.polldisplayresults}
</div>
