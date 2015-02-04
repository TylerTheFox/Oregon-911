<?PHP ?>
<nav id = "menu">
    <ul>
        <li><a href = "./">Incident Map</a></li>
        <li><a href = "./call-list">Call List</a></li>
        <li><a href = "./social">Social Media</a></li>
        <li><a href = "./scanners">Scanners</a>
            <ul>
                <li><a href = "./scanners?recordings=Y">Recording</a></li>
            </ul>
        </li>

        <li><a href = "./search?type=map">Search</a>
            <ul>
                <li><a href = "./search?type=advanced">Advanced</a></li>
            </ul>
        </li>
        <li><a>Statistics</a>
            <ul>
                <li><a>Maps</a>
                    <ul>
                        <li><a href = "./maps?mode=24hr">Last 24 Hours</a></li>
                </li>
            </ul>
        </li>
        <li><a>Graphs</a>
            <ul>
                <li><a href = "./graphs?mode=callvolume">Call Volume</a></li>
                <li><a href = "./graphs?mode=yearcalls">Call Volume By County</a>
                <li><a href = "./graphs?mode=calltypevolume&days=30&calltype=FALL">Call Volume By Type</a>
                <li><a href = "./graphs?mode=countyaveragetravel">Response Time</a>
                <li><a href = "./graphs?mode=accidinjnoninj&days=30">Car Accidents</a>
                </li>
            </ul>
        </li>
        <li><a>Tables</a>
            <ul>
                <li><a href = "./tables?mode=avgtraveltable">Agency Response Time</a></li>
                <li><a href = "./tables?mode=avgunit">Average unit per call</a>
                <li><a href = "./tables?mode=calltypes">Call Types</a>
                <li><a href = "./tables?mode=dispatchflags">Flags/Misc</a>
                </li>
            </ul>
        </li>
        </li>
    </ul>
</li>
<li><a>Contact</a>
    <ul>
        <li><a href = "./contact?frame=bug">Report Bug</a></li>
        <li><a href = "./contact?frame=discussion">Discussion Board</a>
        </li>
    </ul>
</li>
<li><a href = "http://media.oregon911.net">Media</a></li>
<?PHP
if ($LoggedIn) {
    echo('        <li><a href = "./logout">Logout</a></li>');
} else {
    echo('        <li><a href = "./login">Login</a></li>');
}
?>
</ul>
</nav>
<?PHP ?>
