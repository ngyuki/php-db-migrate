/* <?php $this->delimiter('//') ?> */
/* <?php if(false): ?> */
delimiter //
/* <?php endif; ?> */

DROP PROCEDURE IF EXISTS sp_sample;
CREATE PROCEDURE sp_sample ()
BEGIN
  SELECT 1 as one FROM DUAL;
END
//

/* <?php if(false): ?> */
delimiter ;
/* <?php endif; ?> */
