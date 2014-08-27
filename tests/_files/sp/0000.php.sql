/* <?php $this->delimiter('//') ?> */
/* <?php if(false): ?> */
delimiter //
/* <?php endif; ?> */

CREATE PROCEDURE sp_sample ()
BEGIN
  SELECT 1 as one FROM DUAL;
END
//

/* <?php if(false): ?> */
delimiter ;
/* <?php endif; ?> */
