## About Cdarr

Cdarr is a Transcoding tool meant to be used with Sonarr and Radarr built on top of Handbrake.

When you setup Sonarr and Radarr with Plex or Emby you soon run into the problem that most off your video's need te be transcoded on the fly, causing a massive load on your server and limiting even the best servers to 2/3 people watching at the same time.

By transcoding your videos to a generic format before sending them to Plex or Emby you can avoid on the fly transcoding. These tools will then "DirectStream" them, using almost no CPU, only their internet bandwidth. Cdarr does just that. 

First it immediatly hides the video from Plex/Emby by putting a dot in-front of the filename.

Then it transcodes the videos in the following way:
* Removing all subtitles
* Converting all audio tracks to Stereo AAC
* Changing the container and extension to MP4
* Forcing the x264 encoder with the lowest posible encoder profile for the resolution

Beside that Cdarr also minimizes disk space by using the special rate control system created by [Don Melton](https://github.com/donmelton/video_transcoding#how-my-simple-and-special-ratecontrol-systems-work) I ported his code for Cdarr. It makes sure you see no noticible video quality loss and save a ton of space in most situations.

Video transcodes are put in a queue so you wont kill your CPU. The hiding of the videos is always instant. 

## Usage

### docker cli

Cdarr assumes you use the Sonarr and Radarr docker containers provided by linuxserver. I only tested it with their preview versions (Sonarr 3 and Radarr 3).
```
docker run \
  --name=cdarr \
  -e PUID=1000 \
  -e PGID=1000 \
  -e TZ=Europe/London \  
  -p 5757:5757 \
  -v /path/to/data:/config \
  -v /path/to/tvseries:/tv \
  -v /path/to/movies:/movies \  
  --restart unless-stopped \
  jbreuer95/cdarr
```

Now in Sonarr go to: `Settings -> Connect -> + -> Webhook`  
In the URL add the url to you cdarr docker (internal or external) + /api/sonarr
You can leave all the other settings to their defaults

Now in Radarr go to:` Settings -> Connect -> + -> Webhook`  
In the URL add the url to you cdarr docker (internal or external) + /api/radarr
You can leave all the other settings to their defaults

## Contributing

Thank you for considering contributing to Cdarr! You can make a pull request and i will look into it as soon as i can.

## License

Cdarr is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
