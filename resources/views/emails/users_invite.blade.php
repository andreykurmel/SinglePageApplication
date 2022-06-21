<div>
    <p>
        <img src="{{ $logo_url }}" width="275">
    </p>
    <p>
        <b>{{ $user_name }}</b> ({{ $user_email }}) has invited you to experience <b>TablDA - The Digital Infrastructure for Creation, Collaboration & Productivity.</b>
    </p>
    <p>
        Click <a href="{{ $link }}" target="_blank">Invitation</a> or copy the following link and paste it to browser address bar to accept:
    </p>
    <p>
        {{ $link }}
    </p>
    <p>
        TablDA is
    </p>

    <p>
        <ul>
            <li>A Space for Creating, Managing and Sharing Tabulated Data. </li>
          <li>A Foundation for Creating Apps Based on Relational Tables.</li>
          <li>A Platform Facilitating Table-centered Work Collaboration.</li>
      </ul>  
  </p>
  <p>
    Look forward to seeing you there.<br>
    <b>{{ $user_name }}</b>
    The TablDA Team.
</p>

</div>
<style>
p {
    font-size: 20px;
}
</style>