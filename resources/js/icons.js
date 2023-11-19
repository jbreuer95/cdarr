import { library } from "@fortawesome/fontawesome-svg-core";
import { config } from "@fortawesome/fontawesome-svg-core";

import { faHeart } from "@fortawesome/free-solid-svg-icons/faHeart";
import { faUser } from "@fortawesome/free-solid-svg-icons/faUser";
import { faPowerOff } from "@fortawesome/free-solid-svg-icons/faPowerOff";
import { faRotate } from "@fortawesome/free-solid-svg-icons/faRotate";
import { faRightFromBracket } from "@fortawesome/free-solid-svg-icons/faRightFromBracket";
import { faMagnifyingGlass } from "@fortawesome/free-solid-svg-icons/faMagnifyingGlass";
import { faBars } from "@fortawesome/free-solid-svg-icons/faBars";
import { faTv } from "@fortawesome/free-solid-svg-icons/faTv";
import { faFilm } from "@fortawesome/free-solid-svg-icons/faFilm";
import { faClockRotateLeft } from "@fortawesome/free-solid-svg-icons/faClockRotateLeft";
import { faGear } from "@fortawesome/free-solid-svg-icons/faGear";
import { faSliders } from "@fortawesome/free-solid-svg-icons/faSliders";
import { faLaptop } from "@fortawesome/free-solid-svg-icons/faLaptop";
import { faFloppyDisk } from "@fortawesome/free-solid-svg-icons/faFloppyDisk";
import { faVial } from "@fortawesome/free-solid-svg-icons/faVial";
import { faCheck } from "@fortawesome/free-solid-svg-icons/faCheck";
import { faTrashAlt } from "@fortawesome/free-solid-svg-icons/faTrashAlt";

export default () => {
    library.add(
        faHeart,
        faUser,
        faPowerOff,
        faRotate,
        faRightFromBracket,
        faMagnifyingGlass,
        faBars,
        faTv,
        faFilm,
        faClockRotateLeft,
        faGear,
        faSliders,
        faLaptop,
        faFloppyDisk,
        faVial,
        faCheck,
        faTrashAlt,
    );
    config.styleDefault = "solid";
};
