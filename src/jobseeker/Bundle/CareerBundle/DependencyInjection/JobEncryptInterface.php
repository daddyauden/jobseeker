<?php

namespace jobseeker\Bundle\CareerBundle\DependencyInjection;

interface JobEncryptInterface
{

    /**
     * length = 50
     */
    const SEARCH_CONDITION_SALT = "[[ykcynU]=yC/S-=_}c+D2\6%&^]H%3NYjj*Qv!!^;`\trN*gd";
    const DELIVERY_SALT = "V_,ozEsxU}m()Q+-:abh2+wTV]KR;f?Zw^Car(=NVL:&G'<N`]";
    const RESERVE_SALT = "?J_/z{:SW>-&mc<}kh%TQV?V_.(~9&,S(tU.:bGxZ(7fPPW5HR";
    const SCHEDULE_SALT = "ecDCkr6=@?g!m6_\\ofj=2{>{8FY%fmX+@Sz&62W6(MKM-d}4%";
    const EMPLOYEE_SHOW_SALT = "A[F#-eyv>(L<<}%_}UAzM-~%G?:wig_+L^Li'4CSTEc,eA>_fM";
    const JOB_VIEW_SALT = "]Y_dh/FH6t8TY9M&&qFK~CnC2\xkR+Q+mG9h9g>-iD]mv?,o{m";
    const SENDMAIL_SALT = "HP11dYF<fYJFrj10APLyn3XlXsfBhp&}>)(#T[yh8sS7|db>Tx";

}
